@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded shadow p-6">
    <h1 class="text-xl font-semibold mb-2">Welcome, {{ auth()->user()->name }} 👋</h1>
    <p class="text-gray-500 mb-4">
        You're logged in as <strong>{{ auth()->user()->role->label ?? 'User' }}</strong>.
    </p>

    <div class="flex gap-3">
        <a href="{{ route('tasks.index') }}" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
            Go to Tasks
        </a>
        <a href="{{ route('chat.index') }}" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900">
            Chat with AI
        </a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.reports') }}" class="bg-emerald-600 text-white rounded px-4 py-2 text-sm hover:bg-emerald-700">
                View Reports
            </a>
        @endif
    </div>
</div>
@endsection
