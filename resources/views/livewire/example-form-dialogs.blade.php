<x-signal.dialog
    title="Demo submitted"
    title-id="example-submitted-title"
    aria-describedby="example-submitted-message"
    :auto-open="false"
    :show-close="false"
    :dismiss-on-backdrop="false"
    x-ref="submittedDialog"
>
    <p class="dialog-example-description" id="example-submitted-message">Your form passed validation. This was a demo submission.</p>
    <form method="dialog" class="example-form-footer">
        <button class="primary-button" autofocus>OK</button>
    </form>
</x-signal.dialog>

<x-signal.dialog
    :title="$example === 'dedicated' ? 'Clear this form?' : 'Discard your changes?'"
    title-id="example-discard-title"
    role="alertdialog"
    aria-describedby="example-discard-message"
    :auto-open="false"
    :show-close="false"
    :dismiss-on-backdrop="false"
    x-ref="discardDialog"
    x-on:close="if ($el.returnValue === 'discard') $wire.discard()"
>
    <p class="dialog-example-description" id="example-discard-message">This removes the values you entered in this form.</p>
    <form method="dialog" class="example-form-footer">
        <button class="secondary-button" value="cancel" autofocus>Keep editing</button>
        <button class="primary-button" value="discard">{{ $example === 'dedicated' ? 'Clear form' : 'Discard changes' }}</button>
    </form>
</x-signal.dialog>

<x-signal.dialog
    :title="'Edit '.$promptField['label']"
    title-id="example-title-prompt"
    :auto-open="false"
    :show-close="false"
    :dismiss-on-backdrop="false"
    x-ref="titlePrompt"
    x-on:cancel="if ($el.querySelector('button[type=submit]').disabled) $event.preventDefault()"
>
    <form wire:submit="applyTitlePrompt" novalidate>
        <div class="example-field dialog-example-description">
            <label for="example-prompt-value">{{ $promptField['label'] }}</label>
            <input
                class="form-control"
                type="text"
                id="example-prompt-value"
                wire:model="promptValue"
                autofocus
                required
                @error('promptValue') aria-invalid="true" aria-describedby="example-prompt-value-error" @enderror
            >
            @error('promptValue')
                <p id="example-prompt-value-error" class="example-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div class="example-form-footer">
            <button type="button" class="secondary-button" x-on:click="$refs.titlePrompt.close()" wire:loading.attr="disabled" wire:target="applyTitlePrompt">Cancel</button>
            <button type="submit" class="primary-button" wire:loading.attr="disabled" wire:target="applyTitlePrompt">Apply to form</button>
        </div>
    </form>
</x-signal.dialog>
