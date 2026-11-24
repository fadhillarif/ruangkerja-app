<?php

namespace App\Http\Controllers;

use App\Models\ActivityEvent;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $department = Department::whereHas('members', fn ($query) => $query->where('users.id', $request->user()->id))
            ->with('members')
            ->firstOrFail();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'paused', 'completed'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'owner_id' => ['nullable', 'integer'],
            'overdue' => ['nullable', Rule::in(['1'])],
        ]);

        $projectsQuery = $department->projects()
            ->with('owner')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
                'tasks as blocked_tasks_count' => fn ($query) => $query->where('status', 'blocked'),
            ]);

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $projectsQuery->where(fn ($query) => $query
                ->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$search.'%'])
                ->orWhereRaw('LOWER(description) LIKE LOWER(?)', ['%'.$search.'%']));
        }

        if (! empty($filters['status'])) {
            $projectsQuery->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $projectsQuery->where('priority', $filters['priority']);
        }

        if (! empty($filters['owner_id'])) {
            $projectsQuery->where('owner_id', $filters['owner_id']);
        }

        if (! empty($filters['overdue'])) {
            $projectsQuery->whereNotNull('due_at')
                ->whereDate('due_at', '<', today())
                ->where('status', '!=', 'completed');
        }

        $projects = $projectsQuery->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'paused' THEN 1 ELSE 2 END")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderBy('name')
            ->get();

        $summaryProjects = $department->projects()->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            'tasks as blocked_tasks_count' => fn ($query) => $query->where('status', 'blocked'),
        ])->get();

        $projectCount = $summaryProjects->count();
        $activeProjectCount = $summaryProjects->where('status', 'active')->count();
        $taskCount = $summaryProjects->sum('tasks_count');
        $completedCount = $summaryProjects->sum('completed_tasks_count');
        $blockedCount = $summaryProjects->sum('blocked_tasks_count');
        $overallProgress = $taskCount > 0 ? (int) round($completedCount / $taskCount * 100) : 0;
        $canManageProjects = $department->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotIn('role', ['owner', 'leader'])
            ->exists();

        return view('projects.index', compact(
            'department', 'projects', 'filters', 'projectCount', 'activeProjectCount', 'taskCount',
            'completedCount', 'blockedCount', 'overallProgress', 'canManageProjects'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $department = Department::whereHas('members', fn ($query) => $query->where('users.id', $request->user()->id))->firstOrFail();
        $this->authorizeProjectManagement($department, $request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'paused', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_at' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $department->id)],
        ]);

        $project = $department->projects()->create(array_merge($data, [
            'owner_id' => $data['owner_id'] ?? $request->user()->id,
        ]));

        $this->log($project, $request, 'project.created', 'Created project "'.$project->name.'".');

        return redirect()->route('projects.index')->with('status', 'Proyek berhasil dibuat.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectMembership($project, $request);
        $this->authorizeProjectManagement($project->department, $request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'paused', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_at' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $project->department_id)],
        ]);

        $project->fill($data);
        $changedFields = array_values(array_intersect(
            ['name', 'description', 'status', 'priority', 'due_at', 'owner_id'],
            array_keys($project->getDirty())
        ));
        $project->save();

        if ($changedFields !== []) {
            $this->log($project, $request, 'project.updated', 'Updated project details: '.implode(', ', $changedFields).'.');
        }

        return back()->with('status', 'Perubahan proyek tersimpan.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorizeProjectMembership($project, $request);
        $project->load([
            'tasks' => fn ($query) => $query->orderBy('status')->orderBy('position')->orderBy('id'),
            'tasks.assignee', 'owner', 'department.members',
        ]);
        $tasksByStatus = $project->tasks->groupBy('status');
        $taskCount = $project->tasks->count();
        $completedCount = $project->tasks->where('status', 'done')->count();
        $progress = $taskCount > 0 ? (int) round($completedCount / $taskCount * 100) : 0;
        $activityEvents = ActivityEvent::with('actor')
            ->where('project_id', $project->id)
            ->latest('created_at')
            ->limit(20)
            ->get();
        $canManageProjects = $project->department->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotIn('role', ['owner', 'leader'])
            ->exists();
        $canManageTasks = $canManageProjects;

        return view('projects.show', compact('project', 'tasksByStatus', 'taskCount', 'completedCount', 'progress', 'activityEvents', 'canManageProjects', 'canManageTasks'));
    }

    private function authorizeProjectMembership(Project $project, Request $request): void
    {
        abort_unless($project->department->members()->where('users.id', $request->user()->id)->exists(), 403);
    }

    private function authorizeProjectManagement(Department $department, Request $request): void
    {
        abort_unless($department->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotIn('role', ['owner', 'leader'])
            ->exists(), 403);
    }

    private function log(Project $project, Request $request, string $action, string $summary): void
    {
        ActivityEvent::create([
            'department_id' => $project->department_id,
            'actor_id' => $request->user()->id,
            'project_id' => $project->id,
            'action' => $action,
            'summary' => $summary,
            'created_at' => now(),
        ]);
    }
}
