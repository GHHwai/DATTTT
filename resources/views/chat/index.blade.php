@extends('layouts.app')

@section('title', 'AI Chat')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded shadow flex flex-col h-[70vh]">
    <div class="px-4 py-3 border-b font-semibold">AI Assistant</div>

    <div id="chat-log" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
        @foreach ($messages as $message)
            <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] rounded-lg px-3 py-2 text-sm
                    {{ $message->role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                    {{ $message->content }}
                </div>
            </div>
        @endforeach
    </div>

    <form id="chat-form" class="border-t p-3 flex gap-2">
        <input id="chat-input" type="text" placeholder="Type a message..." autocomplete="off"
               class="flex-1 border rounded px-3 py-2 text-sm" required>
        <button type="submit" class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
            Send
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const log = document.getElementById('chat-log');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function appendBubble(text, role) {
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = `max-w-[75%] rounded-lg px-3 py-2 text-sm ${
            role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'
        }`;
        bubble.textContent = text;

        wrapper.appendChild(bubble);
        log.appendChild(wrapper);
        log.scrollTop = log.scrollHeight;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        appendBubble(message, 'user');
        input.value = '';
        input.disabled = true;

        try {
            const response = await fetch('{{ route('chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json();
            appendBubble(data.reply ?? 'Something went wrong.', 'assistant');
        } catch (err) {
            appendBubble('Network error — please try again.', 'assistant');
        } finally {
            input.disabled = false;
            input.focus();
        }
    });
</script>
@endpush
