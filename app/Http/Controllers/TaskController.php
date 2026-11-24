<?php

namespace App\Http\Controllers;

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
            'status' => ['required', 'in:todo,in_progress,blocked,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $project->department_id)],
        ]);
        $project->tasks()->create($data + ['created_by' => $request->user()->id, 'position' => $project->tasks()->where('status', $data['status'])->count()]);

        return back()->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task, $request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:todo,in_progress,blocked,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'integer', Rule::exists('department_members', 'user_id')->where('department_id', $task->project->department_id)],
        ]);
        if ($task->status !== $data['status']) {
            $data['position'] = $task->project->tasks()->where('status', $data['status'])->count();
        }
        $task->update($data);

        return redirect()->route('projects.show', $task->project)->with('status', 'Perubahan tugas tersimpan.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task, $request);
        $project = $task->project;
        abort_unless($project->department->members()->where('users.id', $request->user()->id)->wherePivotIn('role', ['owner', 'leader'])->exists() || $task->created_by === $request->user()->id, 403);
        $task->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Tugas dihapus.');
    }
}
