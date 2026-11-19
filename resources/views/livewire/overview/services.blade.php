@php($services = $snapshot['services'] ?? [])
@php($health = $snapshot['health'] ?? [])

<x-signal.panel class="services-panel" id="services" tabindex="-1" heading="Service Status" :description="count($services).' '.Str::plural('monitored service', count($services))" icon="boxes">
    @if ($services)
        <ul class="service-grid">
            @foreach ($services as $item)
                @php($status = in_array($item['status'], ['healthy', 'degraded', 'down'], true) ? $item['status'] : 'unknown')
                <li wire:key="service-{{ $item['id'] }}">
                    <button type="button" class="service-tile service-{{ $status }}" wire:click="openService('{{ $item['id'] }}')" title="{{ $item['name'] }} · {{ ucfirst($status) }}" aria-label="{{ $item['name'] }}, {{ ucfirst($status) }}, {{ $item['environment'] }}">
                        <x-signal.icon :name="match ($status) { 'healthy' => 'check', 'degraded' => 'minus', 'down' => 'x', default => 'circle-help' }" />
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="service-legend" aria-label="Service totals">
            @foreach (['healthy', 'degraded', 'down', 'unknown'] as $status)
                @if ($status !== 'unknown' || isset($health[$status]))
                    <x-signal.badge :status="$status"><strong>{{ $health[$status] ?? 0 }}</strong> {{ ucfirst($status) }}</x-signal.badge>
                @endif
            @endforeach
        </div>
        <p class="service-caption"><x-signal.icon name="mouse-pointer-2" /> Select a service to inspect its health.</p>
    @else
        <x-signal.empty message="No services in this selection." icon="boxes" />
    @endif
</x-signal.panel>
