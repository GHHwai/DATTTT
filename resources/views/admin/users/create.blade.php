@extends('layouts.app')

@section('title', 'New Account')

@section('content')
<div class="max-w-lg bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">New Account</h1>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role_id" required class="w-full border rounded px-3 py-2">
                <option value="">Select a role...</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2">
        </div>

        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">
            Create Account
        </button>
    </form>
</div>
@endsection
