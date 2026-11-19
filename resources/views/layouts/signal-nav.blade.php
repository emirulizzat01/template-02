<nav class="primary-nav" aria-label="Workspace" x-on:click="if ($event.target.closest('a')) $el.closest('dialog')?.close()">
    @foreach (app(\App\Support\Navigation::class)->groups() as $heading => $items)
        <div class="nav-group">
            <p class="nav-section">{{ $heading }}</p>
            @foreach ($items as $item)
                <x-signal.nav-link
                    :icon="$item['icon']"
                    :href="$item['href']"
                    :active="isset($item['active']) && request()->routeIs($item['active'])"
                    :title="$item['label']"
                >{{ $item['label'] }}</x-signal.nav-link>
            @endforeach
        </div>
    @endforeach
</nav>
