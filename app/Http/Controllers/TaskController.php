<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        // Admins see everyone's tasks, regular users only see their own.
        $tasks = $request->user()->isAdmin()
            ? Task::with('user')->latest()->paginate(10)
            : $request->user()->tasks()->latest()->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('tasks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $request->user()->tasks()->create($validated);

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorizeTaskAccess($request, $task);

        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request, $task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request, $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }

    private function authorizeTaskAccess(Request $request, Task $task): void
    {
        if (! $request->user()->isAdmin() && $task->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
