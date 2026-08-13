@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
@php $isStaff = auth()->user()->hasPermission('tickets.view.all'); @endphp

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">{{ $isStaff ? 'IT Ticket Queue' : 'My Tickets' }}</h1>
    @if (auth()->user()->hasPermission('tickets.create'))
        <a href="{{ route('tickets.create') }}" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
            + New Ticket
        </a>
    @endif
</div>

@if ($isStaff)
    <div class="flex gap-2 mb-4 text-sm">
        @foreach (['unclaimed' => 'Unclaimed', 'mine' => 'Claimed by Me', 'closed' => 'Resolved / Closed'] as $key => $label)
            <a href="{{ route('tickets.index', ['filter' => $key]) }}"
               class="px-3 py-1.5 rounded {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-2">Subject</th>
                <th class="px-4 py-2">Priority</th>
                <th class="px-4 py-2">Status</th>
                @if ($isStaff)
                    <th class="px-4 py-2">Submitted By</th>
                    <th class="px-4 py-2">Assigned To</th>
                @endif
                <th class="px-4 py-2">Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $ticket)
                <tr class="border-t hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-4 py-2">{{ $ticket->subject }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-xs
                            @class([
                                'bg-gray-100 text-gray-700' => $ticket->priority === 'low',
                                'bg-blue-100 text-blue-800' => $ticket->priority === 'medium',
                                'bg-amber-100 text-amber-800' => $ticket->priority === 'high',
                                'bg-red-100 text-red-800' => $ticket->priority === 'urgent',
                            ])">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-xs
                            @class([
                                'bg-yellow-100 text-yellow-800' => $ticket->status === 'open',
                                'bg-blue-100 text-blue-800' => $ticket->status === 'in_progress',
                                'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                                'bg-gray-200 text-gray-700' => $ticket->status === 'closed',
                            ])">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                    </td>
                    @if ($isStaff)
                        <td class="px-4 py-2">{{ $ticket->user->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $ticket->assignee->name ?? '— unclaimed —' }}</td>
                    @endif
                    <td class="px-4 py-2">{{ $ticket->updated_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">No tickets here.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $tickets->links() }}
</div>
@endsection
