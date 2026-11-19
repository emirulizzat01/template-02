<div x-data="{ openSearch() { if (!this.$refs.search.open) this.$refs.search.showModal() } }"
    x-on:keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); openSearch() }"
>
    <button type="button" class="search-trigger" aria-label="Search workspace" x-on:click="openSearch()">
        <x-signal.icon name="search" />
        <span>Search workspace…</span>
        <kbd>Ctrl / ⌘ K</kbd>
    </button>
    <dialog class="signal-dialog search-dialog" wire:ignore.self x-ref="search" aria-label="Search workspace" x-on:click.self="$el.close()">
        <div class="dialog-content">
            <div class="search-dialog-bar">
                <x-signal.icon name="search" />
                <label for="workspace-query" class="sr-only">Search workspace</label>
                <input id="workspace-query" type="search" placeholder="Search services, deployments, incidents…" autofocus maxlength="{{ config('template.search.max_length') }}" wire:model.live.debounce.250ms="query">
                <button type="button" class="icon-button" aria-label="Close search" x-on:click="$refs.search.close()"><x-signal.icon name="x" /></button>
            </div>
            <div class="search-results" aria-live="polite">
                @if (mb_strlen($term) < 2)
                    <p class="search-hint">Enter at least two characters to search.</p>
                @elseif ($unavailable)
                    <p class="search-hint" role="alert">Record search is temporarily unavailable.</p>
                @elseif (! $groups)
                    <p class="search-hint">No matching results.</p>
                @endif
                @foreach ($groups as $label => $matches)
                    <section class="search-group">
                        <h2>{{ $label }}</h2>
                        @foreach ($matches as $match)
                            <a href="{{ $match['url'] }}" x-on:click="if ($el.pathname === window.location.pathname && $el.search === window.location.search) $refs.search.close()">
                                <strong>{{ $match['label'] }}</strong>
                                <span>{{ $match['meta'] }} <x-signal.icon name="arrow-up-right" /></span>
                            </a>
                        @endforeach
                    </section>
                @endforeach
            </div>
        </div>
    </dialog>
</div>
