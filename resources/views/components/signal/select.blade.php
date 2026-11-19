@props(['label', 'id', 'inline' => false])
<div @class(['select-field', 'select-inline' => $inline])>
    <label for="{{ $id }}">{{ $label }}</label>
    <div class="select-control">
        <select {{ $attributes->class(['form-control'])->merge(['id' => $id]) }}>
            {{ $slot }}
        </select>
        <x-signal.icon name="chevron-down" />
    </div>
</div>
