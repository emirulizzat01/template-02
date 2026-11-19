@php($chart = $snapshot['chart'] ?? null)
@php($totals = $chart['totals'] ?? null)

<x-signal.panel class="requests-panel" heading="Request Volume" description="Successful and failed requests per hour" icon="chart-no-axes-combined">
    <x-slot:actions>
        <x-signal.select label="Request period" id="request-period" wire:model.live="period">
            @foreach (config('signal.periods') as $hours)
                <option value="{{ $hours }}">Last {{ $hours }} hours</option>
            @endforeach
        </x-signal.select>
    </x-slot:actions>

    <div class="chart-summary">
        <div>
            <div class="chart-total">{{ $totals ? number_format($totals['requests']) : '—' }}</div>
            <p>{{ $totals && $totals['hours'] < (int) $period ? "Total for {$totals['hours']} of {$period} hours" : 'Total requests' }}</p>
        </div>
        <div class="chart-legend" aria-label="Chart legend">
            <span><i class="chart-swatch swatch-success" aria-hidden="true"></i> Successful</span>
            <span><i class="chart-swatch swatch-error" aria-hidden="true"></i> Failed</span>
        </div>
    </div>

    <div class="plot-container" @if (! $totals) hidden @endif>
        <div class="plot" wire:ignore x-data="signalChart(@js($chart))" x-on:signal-chart.window="update($event.detail.series)" x-on:signal-theme.window="recolor()">
            <canvas x-ref="canvas" role="img" aria-label="Hourly request volume by outcome in UTC. An accessible data table follows."></canvas>
        </div>
    </div>
    @if (! $totals)
        <x-signal.empty message="No request data for this selection." icon="chart-no-axes-combined" />
    @endif

    @if ($totals)
        <details class="chart-details">
            <summary>Hourly data <span>UTC</span></summary>
            <div class="table-scroll">
                <table class="data-table chart-table">
                    <caption class="sr-only">Hourly request volume in UTC</caption>
                    <thead><tr><th scope="col">Hour</th><th scope="col">Successful</th><th scope="col">Failed</th><th scope="col">Total</th></tr></thead>
                    <tbody>
                        @foreach ($chart['labels'] as $index => $label)
                            @php($success = $chart['success'][$index])
                            @php($errors = $chart['errors'][$index])
                            <tr>
                                <th scope="row">{{ $label }}</th>
                                <td>{{ $success === null ? '—' : number_format($success) }}</td>
                                <td>{{ $errors === null ? '—' : number_format($errors) }}</td>
                                <td>{{ $success === null ? '—' : number_format($success + $errors) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
</x-signal.panel>
