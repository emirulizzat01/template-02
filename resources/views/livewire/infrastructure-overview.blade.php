<div class="dashboard-page">
    <header class="page-heading">
        <div>
            <h1>Infrastructure Overview</h1>
            <p>Monitor service health, deployments, and active incidents.</p>
        </div>
        <div class="overview-controls">
            <x-signal.select label="Environment" id="overview-environment" wire:model.live="environment" inline>
                <option value="all">All environments</option>
                @foreach ($environmentOptions as $environmentOption)
                    <option value="{{ $environmentOption['slug'] }}">{{ $environmentOption['name'] }}</option>
                @endforeach
            </x-signal.select>
            <button class="icon-button refresh-button" type="button" wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" aria-label="Refresh dashboard" title="Refresh dashboard">
                <x-signal.icon name="refresh-cw" wire:loading.class="animate-spin" wire:target="refresh" />
            </button>
        </div>
    </header>

    <div @if (! $demo) wire:poll.{{ config('signal.poll_seconds') }}s.visible="refresh" @endif>
        <div class="overview-status" aria-live="polite">
            @if ($unavailable)
                <p role="alert" class="feed-status tone-warning"><span class="status-dot"></span>{{ $snapshot ? 'Refresh failed · showing the last available data' : 'Monitoring data is temporarily unavailable' }}</p>
            @elseif ($snapshot['observed_at'] ?? false)
                <p class="feed-status {{ ! $demo && $snapshot['stale'] ? 'tone-warning' : 'tone-good' }}">
                    <span class="status-dot"></span>
                    {{ $demo ? 'Sample captured' : ($snapshot['stale'] ? 'Partial or stale data' : 'Last updated') }}
                    <x-signal.time :value="$snapshot['observed_at']" absolute />
                </p>
            @else
                <p class="feed-status tone-neutral"><span class="status-dot"></span> No monitoring snapshot</p>
            @endif
            @if ($snapshot['incident_count'] ?? 0)
                <a href="#active-incidents" class="priority-link">
                    <x-signal.icon name="triangle-alert" />
                    {{ $snapshot['incident_count'] }} active {{ Str::plural('incident', $snapshot['incident_count']) }}
                </a>
            @endif
        </div>

        <div class="overview-grid">
            @include('livewire.overview.requests')
            @include('livewire.overview.services')
            @include('livewire.overview.deployments')
            @include('livewire.overview.metrics')
            @include('livewire.overview.incidents')
        </div>
    </div>

    @if ($selectedService)
        <x-signal.dialog :title="$selectedService['name']" title-id="service-details-title" on-close="closeService" wire:key="service-details-{{ $selectedService['id'] }}">
            <dl class="detail-list">
                <div><dt>Status</dt><dd><x-signal.badge :status="$selectedService['status']" /></dd></div>
                <div><dt>Environment</dt><dd>{{ $selectedService['environment'] }}</dd></div>
                <div><dt>Last checked</dt><dd><x-signal.time :value="$selectedService['checked_at']" absolute /></dd></div>
            </dl>
            @if ($selectedService['description'])
                <p class="detail-description">{{ $selectedService['description'] }}</p>
            @endif
            <div class="dialog-actions">
                <a class="secondary-button" href="{{ route('deployments', ['env' => $selectedService['environment_slug'], 'q' => $selectedService['name']]) }}">View deployments <x-signal.icon name="arrow-up-right" /></a>
                <a class="secondary-button" href="{{ route('incidents', ['env' => $selectedService['environment_slug'], 'q' => $selectedService['name']]) }}">View incidents <x-signal.icon name="arrow-up-right" /></a>
            </div>
        </x-signal.dialog>
    @endif
</div>
