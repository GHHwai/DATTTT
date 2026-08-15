@extends('layouts.app')

@section('title', 'Manage Accounts')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Manage Accounts</h1>
    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
        + New Account
    </a>
</div>

@if (session('status'))
    <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex gap-2 mb-4 text-sm">
    <a href="{{ route('admin.users.index') }}"
       class="px-3 py-1.5 rounded {{ !$roleFilter ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
        All
    </a>
    @foreach ($roles as $role)
        <a href="{{ route('admin.users.index', ['role' => $role->name]) }}"
           class="px-3 py-1.5 rounded {{ $roleFilter === $role->name ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
            {{ $role->label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Role</th>
                <th class="px-4 py-2">Joined</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">
                            {{ $user->role->label ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $user->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">Edit</a>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                  onsubmit="return confirm('Delete this account? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No accounts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
