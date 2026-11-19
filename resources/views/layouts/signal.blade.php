<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>
    <meta name="description" content="{{ config('template.description') }}">
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem(@js(config('template.storage_key').'-theme'))
                || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        } catch {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="signalShell(@js(config('template.storage_key')))">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="app-shell" :class="{ 'sidebar-collapsed': collapsed }">
        <aside class="sidebar" aria-label="Primary sidebar">
            <x-signal.brand />
            @include('layouts.signal-nav')
            <div class="sidebar-bottom">
                @include('layouts.signal-user')
            </div>
        </aside>
        <dialog x-ref="navigation" class="mobile-navigation" aria-label="Primary navigation" x-on:click.self="$el.close()">
            <div class="mobile-sidebar">
                <div class="mobile-sidebar-header">
                    <x-signal.brand />
                    <button type="button" class="icon-button" aria-label="Close navigation" x-on:click="$refs.navigation.close()"><x-signal.icon name="x" /></button>
                </div>
                @include('layouts.signal-nav')
                <div class="sidebar-bottom">@include('layouts.signal-user')</div>
            </div>
        </dialog>

        <div class="app-body">
            <header class="topbar">
                <div class="topbar-start">
                    <button type="button" class="icon-button desktop-menu" x-on:click="toggleSidebar()" :aria-expanded="!collapsed" aria-label="Toggle sidebar">
                        <x-signal.icon name="panel-left" />
                    </button>
                    <button type="button" class="icon-button mobile-menu" x-on:click="$refs.navigation.showModal()" aria-label="Open navigation">
                        <x-signal.icon name="menu" />
                    </button>
                    <span class="topbar-divider" aria-hidden="true"></span>
                    <livewire:global-search />
                </div>
                <div class="topbar-actions">
                    @if (app(\App\Monitoring\MonitoringAccess::class)->demo())
                        <span class="demo-label"><x-signal.icon name="flask-conical" /> Demo data</span>
                    @endif
                    <button type="button" class="icon-button theme-button" x-on:click="toggleTheme()" :aria-label="dark ? 'Switch to light theme' : 'Switch to dark theme'" aria-label="Switch theme">
                        <span x-show="!dark"><x-signal.icon name="moon" /></span>
                        <span x-show="dark" x-cloak><x-signal.icon name="sun" /></span>
                    </button>
                </div>
            </header>
            <main id="main-content" class="main-content" tabindex="-1">{{ $slot }}</main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
