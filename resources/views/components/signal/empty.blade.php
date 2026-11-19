@props(['message', 'icon' => 'inbox'])
<div {{ $attributes->class(['empty-state']) }}>
    <x-signal.icon :name="$icon" />
    <p>{{ $message }}</p>
    {{ $slot }}
</div>
