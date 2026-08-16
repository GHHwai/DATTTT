<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">
    <nav class="bg-white border-b">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-14">
            <a href="{{ route('dashboard') }}" class="font-semibold">{{ config('app.name', 'Laravel') }}</a>

            @auth
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('tickets.index') }}" class="hover:underline">
                        {{ auth()->user()->hasPermission('tickets.view.all') ? 'Ticket Queue' : 'My Tickets' }}
                    </a>
                    <a href="{{ route('chat.index') }}" class="hover:underline">AI Chat</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.reports') }}" class="hover:underline text-indigo-600">Admin Reports</a>
                        <a href="{{ route('admin.users.index') }}" class="hover:underline text-indigo-600">Manage Accounts</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-gray-600 hover:underline">
                        <x-avatar :user="auth()->user()" size="sm" />
                        <span>{{ auth()->user()->name }} - {{ auth()->user()->role->label ?? 'No role' }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Log out</button>
                    </form>
                </div>
            @else
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('login') }}" class="hover:underline">Log in</a>
                    <a href="{{ route('register') }}" class="hover:underline">Register</a>
                </div>
            @endauth
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
