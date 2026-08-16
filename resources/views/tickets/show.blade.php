@extends('layouts.app')

@section('title', $ticket->subject)

@section('content')
@php
    $user = auth()->user();
    $isStaff = $user->hasPermission('tickets.view.all');
    $canClaim = $user->hasPermission('tickets.claim');
    $canResolve = $user->hasPermission('tickets.resolve');
@endphp

<div class="max-w-3xl mx-auto space-y-4">

    @if (session('status'))
        <div class="rounded bg-green-100 text-green-800 px-4 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">{{ $ticket->subject }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    #{{ $ticket->id }} &middot; Submitted by {{ $ticket->user->name }} &middot; {{ $ticket->created_at->diffForHumans() }}
                    &middot; {{ ucfirst($ticket->category) }}
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <span class="inline-block px-2 py-0.5 rounded text-xs h-fit
                    @class([
                        'bg-gray-100 text-gray-700' => $ticket->priority === 'low',
                        'bg-blue-100 text-blue-800' => $ticket->priority === 'medium',
                        'bg-amber-100 text-amber-800' => $ticket->priority === 'high',
                        'bg-red-100 text-red-800' => $ticket->priority === 'urgent',
                    ])">
                    {{ ucfirst($ticket->priority) }} priority
                </span>
                <span class="inline-block px-2 py-0.5 rounded text-xs h-fit
                    @class([
                        'bg-yellow-100 text-yellow-800' => $ticket->status === 'open',
                        'bg-blue-100 text-blue-800' => $ticket->status === 'in_progress',
                        'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                        'bg-gray-200 text-gray-700' => $ticket->status === 'closed',
                    ])">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
            </div>
        </div>

        <p class="mt-4 whitespace-pre-line">{{ $ticket->description }}</p>

        @if ($ticket->attachments->isNotEmpty())
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm font-medium mb-2">Attachments</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($ticket->attachments as $attachment)
                        <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}"
                           class="flex items-center gap-2 text-sm border rounded px-3 py-1.5 hover:bg-gray-50">
                            <span>📎</span>
                            <span class="truncate max-w-[160px]">{{ $attachment->original_name }}</span>
                            <span class="text-gray-400 text-xs">({{ $attachment->humanSize() }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($ticket->resolution_notes)
            <div class="mt-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-sm">
                <strong>Resolution notes:</strong>
                <p class="whitespace-pre-line mt-1">{{ $ticket->resolution_notes }}</p>
            </div>
        @endif

        @if ($isStaff)
            <div class="mt-4 pt-4 border-t flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500">
                    Assigned to: <strong>{{ $ticket->assignee->name ?? 'Unclaimed' }}</strong>
                </span>

                @if ($canClaim && $ticket->isUnclaimed())
                    <form method="POST" action="{{ route('tickets.claim', $ticket) }}">
                        @csrf
                        <button class="bg-indigo-600 text-white rounded px-3 py-1.5 text-sm hover:bg-indigo-700">
                            Claim Ticket
                        </button>
                    </form>
                @elseif ($canClaim && ($ticket->assigned_to === $user->id || $user->isAdmin()))
                    <form method="POST" action="{{ route('tickets.unclaim', $ticket) }}">
                        @csrf
                        <button class="border rounded px-3 py-1.5 text-sm hover:bg-gray-100">
                            Release Ticket
                        </button>
                    </form>
                @endif

                @if ($canResolve && $ticket->status !== 'closed')
                    <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="flex flex-wrap items-center gap-2 md:ml-auto">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="border rounded px-2 py-1.5 text-sm">
                            @foreach (['in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="resolution_notes" placeholder="Resolution notes (optional)"
                               class="border rounded px-2 py-1.5 text-sm w-56">
                        <button class="bg-emerald-600 text-white rounded px-3 py-1.5 text-sm hover:bg-emerald-700">
                            Update
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="font-medium mb-3">Activity</h2>

        <div class="space-y-3 mb-4">
            @forelse ($ticket->comments as $comment)
                <div class="text-sm border-l-2 border-gray-200 pl-3">
                    <p class="font-medium">{{ $comment->user->name }}
                        <span class="text-gray-400 font-normal">&middot; {{ $comment->created_at->diffForHumans() }}</span>
                    </p>

                    @if ($comment->body)
                        <p class="whitespace-pre-line">{{ $comment->body }}</p>
                    @endif

                    @if ($comment->attachments->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach ($comment->attachments as $attachment)
                                <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}"
                                   class="flex items-center gap-1 text-xs border rounded px-2 py-1 hover:bg-gray-50">
                                    <span>📎</span>
                                    <span class="truncate max-w-[140px]">{{ $attachment->original_name }}</span>
                                    <span class="text-gray-400">({{ $attachment->humanSize() }})</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400">No comments yet.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" enctype="multipart/form-data" class="space-y-2">
            @csrf

            <div class="flex gap-2">
                <input type="text" name="body" placeholder="Add a comment or update..."
                       class="flex-1 border rounded px-3 py-2 text-sm">
                <button class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
                    Post
                </button>
            </div>

            <input type="file" name="attachments[]" multiple
                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip"
                   class="w-full border rounded px-3 py-1.5 text-xs text-gray-500">

            @error('attachments.*')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('body')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </div>
</div>
@endsection
