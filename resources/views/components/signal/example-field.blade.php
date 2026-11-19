@props(['name', 'field', 'withPrompt' => false])
@php
    $id = 'example-field-'.$name;
    $model = 'values.'.$name;
    $required = in_array('required', explode('|', $field['rules']), true);
    $invalid = $errors->has($model);
    $description = implode(' ', array_filter([isset($field['hint']) ? $id.'-hint' : null, $invalid ? $id.'-error' : null]));
@endphp

<div class="example-field">
    <div class="example-field-heading">
        <label for="{{ $id }}">{{ $field['label'] }} @unless ($required)<span>Optional</span>@endunless</label>
        @if ($withPrompt)
            <button class="panel-link" type="button" wire:click="openTitlePrompt" wire:loading.attr="disabled">Edit via prompt</button>
        @endif
    </div>
    @if ($field['type'] === 'select')
        <div class="select-control">
            <select class="form-control" id="{{ $id }}" wire:model="{{ $model }}" @if ($required) required @endif @if ($invalid) aria-invalid="true" @endif @if ($description) aria-describedby="{{ $description }}" @endif>
                <option value="">Select an option</option>
                @foreach ($field['options'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <x-signal.icon name="chevron-down" />
        </div>
    @elseif ($field['type'] === 'textarea')
        <textarea class="form-control" id="{{ $id }}" wire:model="{{ $model }}" rows="2" placeholder="{{ $field['placeholder'] ?? '' }}" @if ($required) required @endif @if ($invalid) aria-invalid="true" @endif @if ($description) aria-describedby="{{ $description }}" @endif></textarea>
    @else
        <input class="form-control" id="{{ $id }}" wire:model="{{ $model }}" type="{{ $field['type'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if ($required) required @endif @if ($invalid) aria-invalid="true" @endif @if ($description) aria-describedby="{{ $description }}" @endif>
    @endif
    @isset ($field['hint'])
        <p class="example-field-hint" id="{{ $id }}-hint">{{ $field['hint'] }}</p>
    @endisset
    @error ($model)
        <p class="example-field-error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
</div>
