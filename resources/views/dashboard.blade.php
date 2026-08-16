@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded shadow p-6">
    <div class="flex items-center gap-3 mb-4">
        <x-avatar :user="auth()->user()" size="md" />
        <div>
            <h1 class="text-xl font-semibold">
                Welcome, {{ auth()->user()->name }} - {{ auth()->user()->role->label ?? 'No role' }} 👋
            </h1>
        </div>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('tickets.index') }}" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
            {{ auth()->user()->hasPermission('tickets.view.all') ? 'Go to Ticket Queue' : 'My Tickets' }}
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
