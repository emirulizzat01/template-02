<form class="example-form" wire:submit="submit" novalidate>
    <div class="example-form-body">
        <div class="example-form-intro">
            <p>Demo only. Submissions do not change monitoring records.</p>
            <button class="secondary-button" type="button" wire:click="fillSample" wire:loading.attr="disabled">Fill sample data</button>
        </div>

        @foreach ($definition['sections'] as $section)
            <fieldset class="example-fieldset" wire:key="section-{{ $loop->index }}">
                <legend @class(['sr-only' => count($definition['sections']) === 1])>{{ $section['title'] }}</legend>
                <div @class(['example-fields', 'example-fields-pairs' => $dedicated])>
                    @foreach ($section['fields'] as $name => $field)
                        <x-signal.example-field :name="$name" :field="$field" :with-prompt="$name === $promptName" />
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>

    <div class="example-form-footer">
        @if ($errors->has('values.*'))
            <p class="example-message tone-critical" role="alert">Review the highlighted fields and try again.</p>
        @endif

        @if ($submitted)
            <p class="example-message tone-good" role="status"><x-signal.icon name="circle-check" /> Demo submitted successfully.</p>
        @endif
        @if ($dedicated)
            <button class="secondary-button" type="button" wire:click="requestDiscard" wire:loading.attr="disabled">Clear form</button>
        @else
            <button class="secondary-button" type="button" wire:click="requestDiscard" wire:loading.attr="disabled">Close</button>
        @endif
        <button class="primary-button" type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Submit demo</span>
            <span wire:loading wire:target="submit">Validating…</span>
        </button>
    </div>
</form>
