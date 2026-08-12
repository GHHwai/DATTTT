@extends('layouts.app')

@section('title', 'New Task')

@section('content')
<div class="max-w-lg bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">New Task</h1>

    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
        @csrf
        @include('tasks._form', ['task' => null])

        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">
            Create Task
        </button>
    </form>
</div>
@endsection
