@if ($errors->any())
    <div class="rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">Title</label>
    <input type="text" name="title" value="{{ old('title', $task->title ?? '') }}" required
           class="w-full border rounded px-3 py-2">
</div>

<div>
    <label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" rows="4"
              class="w-full border rounded px-3 py-2">{{ old('description', $task->description ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium mb-1">Status</label>
    <select name="status" class="w-full border rounded px-3 py-2">
        @foreach (['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $task->status ?? 'pending') === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>
