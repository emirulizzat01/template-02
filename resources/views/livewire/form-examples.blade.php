<div
    class="form-examples"
    x-data
    x-on:example-discard-requested="$refs.discardDialog.returnValue = ''; $refs.discardDialog.showModal()"
    x-on:example-close-requested="$el.querySelector('#example-form-dialog')?.close()"
    x-on:example-prompt-opened="$refs.titlePrompt.showModal()"
    x-on:example-prompt-applied="$refs.titlePrompt.close()"
    x-on:example-submitted="$refs.submittedDialog.showModal()"
>
    @if ($example === 'dedicated')
        <header class="page-heading">
            <div>
                <a href="{{ route('form-examples.index') }}" class="back-link"><x-signal.icon name="arrow-left" /> Form Examples</a>
                <h1>{{ $definition['title'] }}</h1>
                <p>{{ $definition['description'] }}</p>
            </div>
        </header>
        <div class="panel example-page-panel">
            @include('livewire.example-form', ['dedicated' => true])
        </div>
    @else
        <header class="page-heading">
            <div>
                <h1>Form Examples</h1>
                <p>Try form layouts with prompts, confirmations, and submission alerts.</p>
            </div>
        </header>

        <div class="example-gallery">
            @foreach ($examples as $kind => $item)
                @php($count = collect($item['sections'])->sum(fn ($section) => count($section['fields'])))
                <x-signal.panel class="example-card" :heading="$item['pattern']" :icon="$item['icon']">
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['description'] }}</p>
                    <dl class="example-ranges">
                        <div><dt>Example</dt><dd>{{ $count }} fields</dd></div>
                        <div><dt>Ideal</dt><dd>{{ $item['ideal'] }}</dd></div>
                        <div><dt>Recommended maximum</dt><dd>{{ $item['maximum'] }}</dd></div>
                    </dl>
                    @if ($kind === 'dedicated')
                        <a class="secondary-button" href="{{ route('form-examples.dedicated') }}">Open dedicated page <x-signal.icon name="arrow-up-right" /></a>
                    @else
                        <button class="secondary-button" type="button" wire:click="openExample('{{ $kind }}')" wire:loading.attr="disabled">
                            {{ $kind === 'modal' ? 'Open modal' : 'Open side drawer' }} <x-signal.icon name="arrow-up-right" />
                        </button>
                    @endif
                </x-signal.panel>
            @endforeach
        </div>

        <livewire:dialog-examples />

        @if ($definition)
            <x-signal.dialog
                :title="$definition['title']"
                title-id="example-dialog-title"
                id="example-form-dialog"
                on-close="closeExample"
                close-action="$wire.requestDiscard()"
                x-on:cancel.prevent="if (!$el.querySelector('button[type=submit]').disabled) $wire.requestDiscard()"
                close-label="Close example"
                :dismiss-on-backdrop="false"
                :class="$example === 'drawer' ? 'example-dialog example-drawer' : 'example-dialog'"
                wire:key="example-{{ $example }}"
            >
                @include('livewire.example-form', ['dedicated' => false])
            </x-signal.dialog>
        @endif
    @endif

    @if ($promptField)
        @include('livewire.example-form-dialogs')
    @endif
</div>
