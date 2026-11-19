<x-signal.panel class="incidents-panel" id="active-incidents" tabindex="-1" heading="Active Incidents" :description="($snapshot['incident_count'] ?? 0).' '.Str::plural('unresolved issue', $snapshot['incident_count'] ?? 0)" icon="triangle-alert">
    <x-slot:actions>
        <a class="panel-link panel-icon-link" href="{{ route('incidents', array_filter(['env' => $environment === 'all' ? null : $environment])) }}" aria-label="View all incidents" title="View all incidents">
            <x-signal.icon name="arrow-up-right" />
        </a>
    </x-slot:actions>
    @forelse ($snapshot['incidents'] ?? [] as $incident)
        @if ($loop->first) <ul class="incident-list"> @endif
        <li wire:key="incident-{{ $incident['id'] }}">
            <a class="incident-row" href="{{ route('incidents', ['env' => $incident['environment_slug'], 'record' => $incident['id']]) }}">
                <strong>{{ $incident['title'] }}</strong>
                <div>
                    <x-signal.badge :status="$incident['severity']" />
                    <x-signal.time class="record-time" :value="$incident['opened_at']" />
                </div>
            </a>
        </li>
        @if ($loop->last) </ul> @endif
    @empty
        <x-signal.empty message="No active incidents." icon="circle-check" />
    @endforelse
</x-signal.panel>
