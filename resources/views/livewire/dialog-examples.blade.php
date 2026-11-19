<section
    class="mt-8"
    id="dialog-examples"
    aria-labelledby="dialog-examples-heading"
    x-data="{
        alertResult: '',
        promptResult: '',
        openDialog(dialog) {
            dialog.returnValue = '';
            dialog.showModal();
        },
    }"
    x-on:dialog-example-label-accepted="
        if ($refs.promptDialog.open) {
            promptResult = 'Label submitted: ' + $event.detail.label;
            $refs.promptDialog.close('submitted');
        }
    "
>
    <h2 id="dialog-examples-heading" class="mb-3 text-lg font-semibold">Dialog Examples</h2>

    <div class="example-gallery">
        <x-signal.panel class="example-card dialog-example-card" heading="alert()" icon="circle-check">
            <p>Show a message that requires acknowledgement.</p>
            <button type="button" class="secondary-button" x-on:click="openDialog($refs.alertDialog)">Try alert</button>
            <div class="dialog-example-result" role="status" x-text="alertResult"></div>
        </x-signal.panel>

        <x-signal.panel class="example-card dialog-example-card" heading="confirm()" icon="triangle-alert">
            <p>Ask for approval before acknowledging a demo incident.</p>
            <button type="button" class="secondary-button" x-on:click="openDialog($refs.confirmDialog)" wire:loading.attr="disabled" wire:target="resolveConfirmation">Try confirm</button>
            <div class="dialog-example-result" role="status">{{ $confirmationResult }}</div>
        </x-signal.panel>

        <x-signal.panel class="example-card dialog-example-card" heading="prompt()" icon="circle-help">
            <p>Collect and validate a short service label.</p>
            <button type="button" class="secondary-button" x-on:click="openDialog($refs.promptDialog)" wire:loading.attr="disabled" wire:target="resetLabel,submitLabel">Try prompt</button>
            <div class="dialog-example-result" role="status" x-text="promptResult"></div>
        </x-signal.panel>
    </div>

    <x-signal.dialog
        title="Connection restored"
        title-id="demo-alert-title"
        aria-describedby="demo-alert-message"
        :auto-open="false"
        :show-close="false"
        :dismiss-on-backdrop="false"
        x-ref="alertDialog"
        x-on:close="alertResult = $el.returnValue === 'ok' ? 'Alert acknowledged.' : 'Alert dismissed.'"
    >
        <p class="dialog-example-description" id="demo-alert-message">The demo monitor is receiving health checks again.</p>
        <form method="dialog" class="example-form-footer">
            <button class="primary-button" value="ok" autofocus>OK</button>
        </form>
    </x-signal.dialog>

    <x-signal.dialog
        title="Acknowledge the demo incident?"
        title-id="demo-confirm-title"
        role="alertdialog"
        aria-describedby="demo-confirm-message"
        :auto-open="false"
        :show-close="false"
        :dismiss-on-backdrop="false"
        x-ref="confirmDialog"
        x-on:close="$wire.resolveConfirmation($el.returnValue === 'confirm')"
    >
        <p class="dialog-example-description" id="demo-confirm-message">Confirm to mark the sample incident as acknowledged.</p>
        <form method="dialog" class="example-form-footer">
            <button class="secondary-button" value="cancel" autofocus>Cancel</button>
            <button class="primary-button" value="confirm">Confirm</button>
        </form>
    </x-signal.dialog>

    <x-signal.dialog
        title="Enter a service label"
        title-id="demo-prompt-title"
        :auto-open="false"
        :show-close="false"
        :dismiss-on-backdrop="false"
        x-ref="promptDialog"
        x-on:close="
            if ($el.returnValue !== 'submitted') {
                promptResult = 'Prompt cancelled.';
                $wire.resetLabel();
            }
        "
    >
        <form wire:submit="submitLabel" novalidate>
            <div class="example-field dialog-example-description">
                <label for="demo-prompt-label">Service label</label>
                <input
                    class="form-control"
                    id="demo-prompt-label"
                    type="text"
                    wire:model="label"
                    placeholder="e.g. Gateway API"
                    required
                    autofocus
                    aria-describedby="demo-prompt-hint{{ $errors->has('label') ? ' demo-prompt-error' : '' }}"
                    @error('label') aria-invalid="true" @enderror
                >
                <p id="demo-prompt-hint" class="example-field-hint">Use 2–40 characters.</p>
                @error('label')
                    <p id="demo-prompt-error" class="example-field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="example-form-footer">
                <button type="button" class="secondary-button" x-on:click="$refs.promptDialog.close('cancel')" wire:loading.attr="disabled" wire:target="submitLabel">Cancel</button>
                <button type="submit" class="primary-button" wire:loading.attr="disabled" wire:target="submitLabel">Submit label</button>
            </div>
        </form>
    </x-signal.dialog>
</section>
