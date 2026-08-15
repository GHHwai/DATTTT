@props(['user', 'size' => 'md'])

@php
    $sizeClasses = match ($size) {
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-24 h-24 text-2xl',
        default => 'w-10 h-10 text-sm',
    };

    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $palette = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-sky-500', 'bg-purple-500'];
    $color = $palette[crc32($user->email) % count($palette)];

    $avatarUrl = $user->avatarUrl();
@endphp

@if ($avatarUrl)
    <img
        src="{{ $avatarUrl }}"
        alt="{{ $user->name }}"
        {{ $attributes->merge(['class' => "$sizeClasses rounded-full object-cover shrink-0"]) }}
    >
@else
    <div {{ $attributes->merge(['class' => "$sizeClasses rounded-full flex items-center justify-center text-white font-semibold shrink-0 $color"]) }}>
        {{ $initials ?: '?' }}
    </div>
@endif
