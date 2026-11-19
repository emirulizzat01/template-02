<?php

namespace Database\Seeders;

use App\Monitoring\MonitoringAccess;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MonitoringDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app(MonitoringAccess::class)->demo()) {
            throw new RuntimeException('Demo seeding requires SIGNAL_DEMO=true in a local or testing environment.');
        }
        if (DB::table('environments')->exists()) {
            $this->command?->info('Monitoring records already exist. Demo seeding skipped.');

            return;
        }

        DB::transaction(function (): void {
            $now = CarbonImmutable::now('UTC');
            $production = DB::table('environments')->insertGetId(['slug' => 'production', 'name' => 'Production']);
            $staging = DB::table('environments')->insertGetId(['slug' => 'staging', 'name' => 'Staging']);
            $services = $this->seedServices($production, $staging, $now);
            $this->seedMetrics($production, $staging, $now);

            $deployments = [
                ['api-gateway', '2.14.0', 'a3f8c21', 'Alex Chen', 'success', 2],
                ['auth-service', '1.8.3', 'b7d4e09', 'Jordan Park', 'rolling', 8],
                ['payment-processor', '3.2.1', 'c1e5f42', 'Sam Rivera', 'success', 15],
                ['notification-svc', '1.4.0', 'd9a0b73', 'Morgan Liu', 'failed', 32],
                ['inventory-api', '2.9.2', 'f758bc1', 'Riley Shah', 'success', 44],
            ];
            for ($n = 0; $n < 16; $n++) {
                $name = array_keys($services)[($n + 5) % count($services)];
                $deployments[] = [$name, "1.9.{$n}", substr(sha1($name.$n), 0, 7), $n % 2 ? 'Morgan Liu' : 'Alex Chen', $n % 5 ? 'success' : 'failed', 78 + $n * 49];
            }
            foreach ($deployments as [$name, $version, $commit, $actor, $status, $minutes]) {
                DB::table('deployments')->insert([
                    'monitored_service_id' => $services[$name], 'version' => $version, 'commit' => $commit,
                    'actor' => $actor, 'status' => $status, 'deployed_at' => $now->subMinutes($minutes),
                ]);
            }

            foreach ([
                ['db-primary', 'DB connection pool exhausted', 'critical', 'investigating', 12, 'The primary database is refusing new connections. The on-call team is reviewing pool saturation.'],
                ['edge-proxy', 'SSL certificate expiring', 'warning', 'open', 28, 'The edge certificate is approaching its expiry date. Rotation is scheduled.'],
                ['worker-pool', 'High memory usage', 'warning', 'monitoring', 63, 'Worker memory exceeded its alert threshold. The team is monitoring recovery.'],
                ['asset-cdn', 'Scheduled maintenance', 'info', 'monitoring', 119, 'Planned maintenance is in progress.'],
                ['rate-limiter', 'API rate limit approaching', 'info', 'open', 165, 'Sustained demand is approaching the configured request limit.'],
                ['cache-primary', 'Cache eviction spike', 'warning', 'resolved', 307, 'The spike subsided after capacity was adjusted.'],
                ['search-api', 'Search index delay', 'warning', 'resolved', 400, 'The search indexing backlog was cleared.'],
            ] as [$name, $title, $severity, $status, $minutes, $description]) {
                DB::table('incidents')->insert([
                    'monitored_service_id' => $services[$name], 'title' => $title, 'description' => $description,
                    'severity' => $severity, 'status' => $status, 'opened_at' => $now->subMinutes($minutes),
                ]);
            }
        });
    }

    private function seedServices(int $production, int $staging, CarbonImmutable $now): array
    {
        $names = [
            'api-gateway', 'auth-service', 'payment-processor', 'notification-svc', 'edge-proxy', 'inventory-api',
            'checkout-api', 'catalog-api', 'event-stream', 'profile-api', 'search-index', 'worker-pool',
            'analytics-api', 'account-api', 'scheduler', 'asset-cdn', 'web-frontend', 'cache-primary',
            'audit-log', 'secrets-manager', 'object-store', 'email-relay', 'message-queue', 'billing-api',
            'webhook-relay', 'reporting-api', 'db-primary', 'rate-limiter', 'file-processor', 'tracing',
            'search-api', 'preview-gateway',
        ];
        $services = [];
        foreach ($names as $position => $name) {
            $status = match ($name) {
                'edge-proxy', 'worker-pool', 'email-relay' => 'degraded',
                'db-primary' => 'down',
                default => 'healthy',
            };
            $services[$name] = DB::table('monitored_services')->insertGetId([
                'environment_id' => $position < 24 || $position === 26 ? $production : $staging,
                'name' => $name,
                'status' => $status,
                'checked_at' => $now->subSeconds(13 + $position),
            ]);
        }

        return $services;
    }

    private function seedMetrics(int $production, int $staging, CarbonImmutable $now): void
    {
        foreach ([$production => [7984, 8000, 1136000, 8000, 672, 800, 47, 50], $staging => [1996, 2000, 284000, 2000, 168, 200, 47, 50]] as $environment => $values) {
            DB::table('monitoring_snapshots')->insert([
                'environment_id' => $environment, 'observed_at' => $now,
                ...array_combine(['healthy_checks', 'total_checks', 'latency_sum_ms', 'latency_samples',
                    'used_capacity', 'total_capacity', 'successful_deployments', 'finished_deployments'], $values),
            ]);
        }

        $dailyPattern = [2100, 1820, 1520, 1370, 1290, 1510, 2010, 2630, 3250, 3710, 3890, 4090,
            3810, 4020, 4230, 4110, 3910, 3730, 3240, 2950, 2730, 2480, 2230, 2050];
        $end = $now->startOfHour();
        $buckets = [];
        for ($hour = 0; $hour < 72; $hour++) {
            $volume = $dailyPattern[$hour % 24] * (0.82 + intdiv($hour, 24) * 0.09);
            $time = $end->subHours(72 - $hour);
            foreach ([$production => 0.83, $staging => 0.17] as $environment => $share) {
                $errors = (int) round($volume * $share * (0.017 + 0.009 * pow(sin($hour * 0.68), 2)));
                $buckets[] = ['environment_id' => $environment, 'bucket_at' => $time, 'success' => (int) round($volume * $share) - $errors, 'errors' => $errors];
            }
        }
        DB::table('request_buckets')->insert($buckets);
    }
}
