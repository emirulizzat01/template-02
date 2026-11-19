@props(['status'])
@php
    $tone = match ($status) {
        'healthy', 'success', 'resolved' => 'good',
        'warning', 'rolling', 'degraded', 'investigating' => 'warning',
        'critical', 'failed', 'down' => 'critical',
        'open', 'monitoring', 'info' => 'info',
        default => 'neutral',
    };
@endphp
<span {{ $attributes->class(['status-badge', 'tone-'.$tone]) }}>
    <span class="status-dot" aria-hidden="true"></span>{{ $slot->isEmpty() ? ucfirst($status) : $slot }}
</span>
