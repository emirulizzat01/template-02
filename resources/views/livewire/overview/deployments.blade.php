<x-signal.panel class="deployments-panel" heading="Deployment Pipeline" description="Latest deployments across services" icon="git-branch">
    <x-slot:actions>
        <a class="panel-link" href="{{ route('deployments', array_filter(['env' => $environment === 'all' ? null : $environment])) }}">
            View all <x-signal.icon name="arrow-up-right" />
        </a>
    </x-slot:actions>
    @forelse ($snapshot['deployments'] ?? [] as $deployment)
        @if ($loop->first) <ol class="deployment-list"> @endif
        <li wire:key="deployment-{{ $deployment['id'] }}">
            <span class="timeline-marker tone-{{ match ($deployment['status']) { 'success' => 'good', 'failed' => 'critical', 'rolling' => 'warning', default => 'neutral' } }}" aria-hidden="true"></span>
            <a class="deployment-row" href="{{ route('deployments', ['env' => $deployment['environment_slug'], 'record' => $deployment['id']]) }}">
                <strong>{{ $deployment['service'] }}</strong>
                <x-signal.badge :status="$deployment['status']" />
                <div class="deployment-meta">
                    <span class="version-tag">v{{ $deployment['version'] }}</span>
                    <span class="commit-code">{{ $deployment['commit'] }}</span>
                    <span class="deployment-actor">{{ $deployment['actor'] }}</span>
                </div>
                <x-signal.time class="record-time" :value="$deployment['deployed_at']" />
            </a>
        </li>
        @if ($loop->last) </ol> @endif
    @empty
        <x-signal.empty message="No recent deployments." />
    @endforelse
</x-signal.panel>
