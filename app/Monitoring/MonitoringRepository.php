<?php

namespace App\Monitoring;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MonitoringRepository
{
    public function services(array $environmentIds): Builder
    {
        return DB::table('monitored_services as s')
            ->join('environments as e', 's.environment_id', '=', 'e.id')
            ->whereIn('e.id', $environmentIds)
            ->select('s.*', 'e.name as environment', 'e.slug as environment_slug');
    }

    public function deployments(array $environmentIds): Builder
    {
        return DB::table('deployments as d')
            ->join('monitored_services as s', 'd.monitored_service_id', '=', 's.id')
            ->join('environments as e', 's.environment_id', '=', 'e.id')
            ->whereIn('e.id', $environmentIds)
            ->select('d.*', 's.name as service', 'e.name as environment', 'e.slug as environment_slug');
    }

    public function incidents(array $environmentIds): Builder
    {
        return DB::table('incidents as i')
            ->join('monitored_services as s', 'i.monitored_service_id', '=', 's.id')
            ->join('environments as e', 's.environment_id', '=', 'e.id')
            ->whereIn('e.id', $environmentIds)
            ->select('i.*', 's.name as service', 'e.name as environment', 'e.slug as environment_slug');
    }

    public function overview(array $environmentIds, int $hours): array
    {
        $services = $this->services($environmentIds)->orderBy('s.id')->get();
        $snapshots = DB::table('monitoring_snapshots')->whereIn('environment_id', $environmentIds)->get();
        $activeIncidents = $this->incidents($environmentIds)->whereIn('i.status', config('signal.active_incident_statuses'));
        $observedAt = $snapshots->min('observed_at');

        $demoReference = app(MonitoringAccess::class)->demo() && $observedAt
            ? CarbonImmutable::parse($observedAt, 'UTC')
            : null;

        return [
            'chart' => $this->requestSeries($environmentIds, $hours, $demoReference),
            'services' => $services->map(fn (object $service): array => (array) $service)->all(),
            'health' => $services->countBy(fn (object $service): string => in_array($service->status, ['healthy', 'degraded', 'down'], true) ? $service->status : 'unknown')->all(),
            'deployments' => $this->deployments($environmentIds)->orderByDesc('d.deployed_at')->orderByDesc('d.id')
                ->limit(config('signal.recent_limit'))->get()->map(fn (object $record): array => (array) $record)->all(),
            'incidents' => (clone $activeIncidents)
                ->orderByRaw("CASE i.severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderByDesc('i.opened_at')->orderByDesc('i.id')->limit(config('signal.recent_limit'))->get()
                ->map(fn (object $record): array => (array) $record)->all(),
            'incident_count' => $activeIncidents->count(),
            'observed_at' => $observedAt,
            'stale' => $observedAt !== null && (
                $snapshots->count() < count($environmentIds)
                || CarbonImmutable::parse($observedAt, 'UTC')->lt(now()->subMinutes(config('signal.stale_after_minutes')))
            ),
            'metrics' => [
                'uptime' => $this->ratio($snapshots->sum('healthy_checks'), $snapshots->sum('total_checks'), 100),
                'latency' => $this->ratio($snapshots->sum('latency_sum_ms'), $snapshots->sum('latency_samples')),
                'load' => $this->ratio($snapshots->sum('used_capacity'), $snapshots->sum('total_capacity'), 100),
                'deploy_rate' => $this->ratio($snapshots->sum('successful_deployments'), $snapshots->sum('finished_deployments'), 100),
            ],
        ];
    }

    private function requestSeries(array $environmentIds, int $hours, ?CarbonImmutable $reference): array
    {
        $end = ($reference ?? CarbonImmutable::now('UTC'))->startOfHour();
        $start = $end->subHours($hours);
        $buckets = DB::table('request_buckets')
            ->whereIn('environment_id', $environmentIds)
            ->where('bucket_at', '>=', $start)
            ->where('bucket_at', '<', $end)
            ->groupBy('bucket_at')
            ->selectRaw('bucket_at, SUM(success) as success_total, SUM(errors) as error_total, COUNT(*) as sources')
            ->get()
            ->keyBy(fn (object $row): string => CarbonImmutable::parse($row->bucket_at, 'UTC')->format('Y-m-d H:i:s'));

        $series = ['labels' => [], 'success' => [], 'errors' => [], 'totals' => null];
        foreach (range(0, $hours - 1) as $hour) {
            $time = $start->addHours($hour);
            $bucket = $buckets->get($time->format('Y-m-d H:i:s'));
            $complete = $bucket && (int) $bucket->sources === count($environmentIds);
            $series['labels'][] = $time->format('M j, H:i');
            $series['success'][] = $complete ? (int) $bucket->success_total : null;
            $series['errors'][] = $complete ? (int) $bucket->error_total : null;
        }

        $available = count(array_filter($series['success'], fn (?int $value): bool => $value !== null));
        if ($available > 0) {
            $series['totals'] = [
                'requests' => array_sum($series['success']) + array_sum($series['errors']),
                'errors' => array_sum($series['errors']),
                'hours' => $available,
            ];
        }

        return $series;
    }

    private function ratio(int|float $numerator, int|float $denominator, int $scale = 1): ?float
    {
        return $denominator > 0 ? $numerator / $denominator * $scale : null;
    }
}
