<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $department = Department::whereHas('members', fn ($query) => $query->where('users.id', $request->user()->id))
            ->with(['projects.tasks', 'projects.owner'])->firstOrFail();

        return view('projects.index', compact('department'));
    }

    public function store(Request $request): RedirectResponse
    {
        $department = Department::whereHas('members', fn ($query) => $query->where('users.id', $request->user()->id))->firstOrFail();
        abort_unless($department->members()->where('users.id', $request->user()->id)->wherePivotIn('role', ['owner', 'leader'])->exists(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,paused,completed'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_at' => ['nullable', 'date'],
        ]);
        $department->projects()->create($data + ['owner_id' => $request->user()->id]);

        return redirect()->route('projects.index')->with('status', 'Proyek berhasil dibuat.');
    }

    public function show(Request $request, Project $project): View
    {
        abort_unless($project->department->members()->where('users.id', $request->user()->id)->exists(), 403);
        $project->load(['tasks' => fn ($query) => $query->orderBy('status')->orderBy('position')->orderBy('id'), 'tasks.assignee', 'owner', 'department.members']);
        $tasksByStatus = $project->tasks->groupBy('status');

        return view('projects.show', compact('project', 'tasksByStatus'));
    }
}
