@props(['status'])

@php
    $tones = match (strtolower((string) $status)) {
        'active' => 'bg-green-100 text-green-700',
        'inactive' => 'bg-red-100 text-red-700',
        'leave' => 'bg-blue-100 text-blue-700',
        'retired' => 'bg-slate-200 text-slate-600',
        default => 'bg-slate-100 text-slate-600',
    };

    $label = strtolower((string) $status) === 'leave' ? 'On Leave' : ucfirst((string) $status);
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $tones }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $label }}
</span>