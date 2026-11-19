@props(['title', 'titleId', 'onClose' => null, 'closeLabel' => 'Close details', 'dismissOnBackdrop' => true, 'autoOpen' => true, 'showClose' => true, 'closeAction' => null])
<dialog
    {{ $attributes->class(['signal-dialog']) }}
    wire:ignore.self
    aria-labelledby="{{ $titleId }}"
    @if ($autoOpen)
        x-data
        x-init="$nextTick(() => $el.showModal())"
    @endif
    @if ($onClose) x-on:close="$wire.{{ $onClose }}()" @endif
    @if ($dismissOnBackdrop) x-on:click.self="$el.close()" @endif
>
    <div class="dialog-content">
        <header class="dialog-header">
            <h2 id="{{ $titleId }}">{{ $title }}</h2>
            @if ($showClose)
                <button
                    type="button"
                    class="icon-button"
                    aria-label="{{ $closeLabel }}"
                    @if ($closeAction)
                        x-on:click="{{ $closeAction }}" wire:loading.attr="disabled"
                    @else
                        x-on:click="$el.closest('dialog').close()"
                    @endif
                    autofocus
                >
                    <x-signal.icon name="x" />
                </button>
            @endif
        </header>
        {{ $slot }}
    </div>
</dialog>
