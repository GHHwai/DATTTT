<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AI Service Desk') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

        <nav class="border-b bg-white">
            <div class="max-w-5xl mx-auto px-4 flex items-center justify-between h-16">
                <span class="font-semibold text-lg">{{ config('app.name', 'AI Service Desk') }}</span>

                <div class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white rounded px-4 py-1.5 hover:bg-indigo-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hover:underline">Log in</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white rounded px-4 py-1.5 hover:bg-indigo-700">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="flex-1">
            <section class="max-w-5xl mx-auto px-4 pt-16 pb-12 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">
                    One desk for tasks, teams, and AI help.
                </h1>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto mb-8">
                    {{ config('app.name', 'AI Service Desk') }} brings task management, role-based access,
                    an AI chat assistant, and live reporting together in one simple app.
                </p>

                <div class="flex items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white rounded px-6 py-2.5 font-medium hover:bg-indigo-700">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white rounded px-6 py-2.5 font-medium hover:bg-indigo-700">
                            Create a free account
                        </a>
                        <a href="{{ route('login') }}" class="border border-gray-300 rounded px-6 py-2.5 font-medium hover:bg-gray-100">
                            Log in
                        </a>
                    @endauth
                </div>
            </section>

            <section class="max-w-5xl mx-auto px-4 pb-20">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="w-10 h-10 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center mb-3">
                            ✅
                        </div>
                        <h3 class="font-semibold mb-1">Task Management</h3>
                        <p class="text-sm text-gray-500">
                            Create, assign, and track tasks through pending, in-progress, and completed states.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="w-10 h-10 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                            🤖
                        </div>
                        <h3 class="font-semibold mb-1">AI Chat Assistant</h3>
                        <p class="text-sm text-gray-500">
                            Every user gets a built-in AI assistant to help answer questions, on a free-tier API.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="w-10 h-10 rounded bg-amber-100 text-amber-600 flex items-center justify-center mb-3">
                            🔐
                        </div>
                        <h3 class="font-semibold mb-1">Roles & Permissions</h3>
                        <p class="text-sm text-gray-500">
                            Fine-grained roles decide who can view, create, edit, or delete what.
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-5">
                        <div class="w-10 h-10 rounded bg-rose-100 text-rose-600 flex items-center justify-center mb-3">
                            📊
                        </div>
                        <h3 class="font-semibold mb-1">Admin Reports</h3>
                        <p class="text-sm text-gray-500">
                            Admins get live charts on tasks, users, and signups — no extra setup required.
                        </p>
                    </div>

                </div>
            </section>
        </main>

        <footer class="border-t bg-white py-6">
            <div class="max-w-5xl mx-auto px-4 text-center text-sm text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'AI Service Desk') }}
            </div>
        </footer>

    </body>
</html>
