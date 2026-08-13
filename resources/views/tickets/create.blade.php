@extends('layouts.app')

@section('title', 'New Ticket')

@section('content')
<div class="max-w-lg bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Submit a Ticket</h1>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                   placeholder="e.g. Laptop won't connect to VPN"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category" class="w-full border rounded px-3 py-2">
                    @foreach (['hardware' => 'Hardware', 'software' => 'Software', 'network' => 'Network', 'account' => 'Account / Access', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select name="priority" class="w-full border rounded px-3 py-2">
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Describe the problem</label>
            <textarea name="description" rows="5" required
                      placeholder="What's happening, what have you tried, error messages, etc."
                      class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">
            Submit Ticket
        </button>
    </form>
</div>
@endsection
