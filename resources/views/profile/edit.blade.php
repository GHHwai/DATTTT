@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-lg mx-auto space-y-6">

    @if (session('status'))
        <div class="rounded bg-green-100 text-green-800 px-4 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-1">My Profile</h1>
        <p class="text-sm text-gray-500 mb-4">
            {{ $user->role->label ?? 'No role assigned' }}
        </p>

        <div class="flex items-center gap-4 mb-6">
            <x-avatar :user="$user" size="lg" />

            <div class="flex flex-col gap-2">
                <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <label class="text-sm bg-gray-800 text-white rounded px-3 py-1.5 cursor-pointer hover:bg-gray-900">
                        Upload photo
                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>

                @if ($user->avatar_path)
                    <form method="POST" action="{{ route('profile.avatar.destroy') }}"
                          onsubmit="return confirm('Remove your profile photo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Remove photo</button>
                    </form>
                @endif

                @error('avatar')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border rounded px-3 py-2">
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full border rounded px-3 py-2">
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
                Save changes
            </button>
        </form>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="font-medium mb-4">Change Password</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Current Password</label>
                <input type="password" name="current_password" required class="w-full border rounded px-3 py-2">
                @error('current_password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">New Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2 text-sm hover:bg-gray-900">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection
