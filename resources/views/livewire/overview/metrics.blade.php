<x-signal.panel class="metrics-panel" heading="Key Metrics" description="Latest performance snapshot" icon="gauge">
    <dl class="metric-list">
        @foreach ([
            ['uptime', 'Uptime', '%', 1, 'good', '24h'],
            ['latency', 'Avg latency', ' ms', 0, 'info', '24h'],
            ['load', 'Server load', '%', 0, 'warning', 'Current'],
            ['deploy_rate', 'Deploy success', '%', 0, 'good', '24h'],
        ] as [$key, $label, $unit, $decimals, $tone, $interval])
            @php($value = $snapshot['metrics'][$key] ?? null)
            <div class="metric">
                <dt>{{ $label }} <span>{{ $interval }}</span></dt>
                <dd>{{ $value === null ? '—' : number_format($value, $decimals).$unit }}</dd>
                @if ($unit === '%' && $value !== null)
                    <span class="metric-track tone-{{ $tone }}" aria-hidden="true">
                        <span style="width: {{ max(0, min(100, $value)) }}%"></span>
                    </span>
                @endif
            </div>
        @endforeach
    </dl>
</x-signal.panel>
