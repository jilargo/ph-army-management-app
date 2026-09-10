@props([
    'personnel' => null,
    'src' => null,
    'name' => null,
    'size' => 'md',
    'class' => '',
])

@php
    $sizes = [
        'xs' => 'h-6 w-6 text-[10px]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-lg',
        'xl' => 'h-24 w-24 text-3xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $imageSrc = $src ?? $personnel?->avatar_url ?? null;

    if (! $name && $personnel) {
        $name = $personnel->full_name;
    }

    $fallback = $name
        ? collect(explode(' ', preg_replace('/\s+/', ' ', trim((string) $name))))
            ->reject(fn ($part) => $part === 'Jr.' || $part === 'Sr.' || $part === 'II' || $part === 'III')
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('')
        : '?';

    $hue = $name ? (crc32((string) $name) % 360) : 200;
@endphp

@if ($imageSrc)
    <img
        src="{{ $imageSrc }}"
        alt="{{ $name ?? 'Avatar' }}"
        {{ $attributes->merge(['class' => 'inline-block shrink-0 rounded-full object-cover '.$sizeClass.' '.$class]) }}
    >
@else
    <span
        {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full font-semibold text-white select-none '.$sizeClass.' '.$class]) }}
        style="background-color: hsl({{ $hue }} 55% 45%)"
    >
        {{ $fallback }}
    </span>
@endif