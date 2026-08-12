@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="max-w-lg bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Edit Task</h1>

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('tasks._form', ['task' => $task])

        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">
            Save Changes
        </button>
    </form>
</div>
@endsection
