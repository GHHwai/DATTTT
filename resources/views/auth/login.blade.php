@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Log in</h1>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border rounded px-3 py-2">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember"> Remember me
        </label>

        <button type="submit" class="w-full bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">
            Log in
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-4">
        Need an account? <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Register</a>
    </p>
</div>
@endsection
