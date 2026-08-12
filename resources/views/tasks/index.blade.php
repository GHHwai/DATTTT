@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">{{ auth()->user()->isAdmin() ? 'All Tasks' : 'My Tasks' }}</h1>
    <a href="{{ route('tasks.create') }}" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
        + New Task
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Status</th>
                @if (auth()->user()->isAdmin())
                    <th class="px-4 py-2">Owner</th>
                @endif
                <th class="px-4 py-2">Created</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $task->title }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-xs
                            @class([
                                'bg-yellow-100 text-yellow-800' => $task->status === 'pending',
                                'bg-blue-100 text-blue-800' => $task->status === 'in_progress',
                                'bg-green-100 text-green-800' => $task->status === 'completed',
                            ])">
                            {{ str_replace('_', ' ', $task->status) }}
                        </span>
                    </td>
                    @if (auth()->user()->isAdmin())
                        <td class="px-4 py-2">{{ $task->user->name ?? '—' }}</td>
                    @endif
                    <td class="px-4 py-2">{{ $task->created_at->diffForHumans() }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:underline">Edit</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No tasks yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $tasks->links() }}
</div>
@endsection
