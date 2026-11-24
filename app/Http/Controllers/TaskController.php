<?php

namespace App\Http\Controllers;

use App\Models\ActivityEvent;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private function authorizeProject(Project $project, Request $request): void
    {
        abort_unless($project->department->members()->where('users.id', $request->user()->id)->exists(), 403);
    }

    private function authorizeTask(Task $task, Request $request): void
    {
        abort_unless($task->project->department->members()->where('users.id', $request->user()->id)->exists(), 403);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project, $request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'blocked', 'done'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $project->department_id)],
        ]);

        $task = $project->tasks()->create($data + [
            'created_by' => $request->user()->id,
            'position' => $project->tasks()->where('status', $data['status'])->count(),
        ]);

        $this->log($task, $request, 'task.created', 'Added task "'.$task->title.'".');

        return back()->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task, $request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'blocked', 'done'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $task->project->department_id)],
        ]);

        $oldStatus = $task->status;
        if ($oldStatus !== $data['status']) {
            $data['position'] = $task->project->tasks()->where('status', $data['status'])->count();
        }

        $task->update($data);
        $changedFields = array_values(array_intersect(
            ['title', 'description', 'assignee_id', 'status', 'priority', 'due_at'],
            array_keys($task->getChanges())
        ));

        if ($changedFields !== []) {
            $statuses = ['todo' => 'To do', 'in_progress' => 'In progress', 'blocked' => 'Blocked', 'done' => 'Done'];
            $action = $oldStatus !== $task->status ? 'task.status_changed' : 'task.updated';
            $summary = $oldStatus !== $task->status
                ? 'Moved "'.$task->title.'" to '.$statuses[$task->status].'.'
                : 'Updated "'.$task->title.'": '.implode(', ', $changedFields).'.';
            $this->log($task, $request, $action, $summary);
        }

        return redirect()->route('projects.show', $task->project)->with('status', 'Perubahan tugas tersimpan.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task, $request);
        $project = $task->project;
        abort_unless($project->department->members()->where('users.id', $request->user()->id)->wherePivotIn('role', ['owner', 'leader'])->exists() || $task->created_by === $request->user()->id, 403);

        $this->log($task, $request, 'task.deleted', 'Deleted task "'.$task->title.'".');
        $task->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Tugas dihapus.');
    }

    private function log(Task $task, Request $request, string $action, string $summary): void
    {
        ActivityEvent::create([
            'department_id' => $task->project->department_id,
            'actor_id' => $request->user()->id,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'action' => $action,
            'summary' => $summary,
            'created_at' => now(),
        ]);
    }
}
