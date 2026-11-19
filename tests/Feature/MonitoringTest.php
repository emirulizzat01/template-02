<?php

namespace Tests\Feature;

use App\Livewire\DeploymentsIndex;
use App\Livewire\GlobalSearch;
use App\Livewire\IncidentsIndex;
use App\Livewire\InfrastructureOverview;
use App\Models\User;
use Database\Seeders\MonitoringDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['signal.demo' => true]);
        $this->seed(MonitoringDemoSeeder::class);
    }

    public function test_dashboard_restores_url_filters_and_refreshes_the_chart(): void
    {
        $this->get('/?env=staging&period=6')
            ->assertOk()
            ->assertSee('7 monitored services')
            ->assertSee('Demo data');

        Livewire::withQueryParams(['env' => 'staging', 'period' => '6'])
            ->test(InfrastructureOverview::class)
            ->assertSet('environment', 'staging')
            ->assertSet('period', '6')
            ->assertSet('snapshot.chart.totals.hours', 6)
            ->set('period', '24')
            ->assertSet('snapshot.chart.totals.hours', 24)
            ->assertDispatched('signal-chart')
            ->set('environment', 'all')
            ->assertSet('snapshot.health.healthy', 28)
            ->assertSet('snapshot.health.degraded', 3)
            ->assertSet('snapshot.health.down', 1);
    }

    public function test_chart_distinguishes_incomplete_hours_from_zero_traffic(): void
    {
        $production = DB::table('environments')->where('slug', 'production')->value('id');
        $hours = DB::table('request_buckets')->where('environment_id', $production)->orderByDesc('bucket_at')->limit(2)->pluck('bucket_at');
        DB::table('request_buckets')->where('bucket_at', $hours[0])->where('environment_id', $production)->delete();
        DB::table('request_buckets')->where('bucket_at', $hours[1])->update(['success' => 0, 'errors' => 0]);

        Livewire::test(InfrastructureOverview::class)
            ->assertSet('snapshot.chart.success.23', null)
            ->assertSet('snapshot.chart.errors.23', null)
            ->assertSet('snapshot.chart.success.22', 0)
            ->assertSet('snapshot.chart.errors.22', 0)
            ->assertSet('snapshot.chart.totals.hours', 23);
    }

    public function test_failed_refresh_only_keeps_data_for_the_same_scope(): void
    {
        $component = Livewire::test(InfrastructureOverview::class)
            ->assertSet('snapshot.incident_count', 5);
        Schema::drop('monitoring_snapshots');

        $component
            ->call('refresh')
            ->assertSet('unavailable', true)
            ->assertSet('snapshot.incident_count', 5)
            ->assertSee('showing the last available data')
            ->set('environment', 'staging')
            ->assertSet('snapshot', [])
            ->assertSee('Monitoring data is temporarily unavailable');
    }

    public function test_deployment_pagination_resets_when_filters_change(): void
    {
        Livewire::test(DeploymentsIndex::class)
            ->assertSee('21 deployments')
            ->call('nextPage')
            ->assertSee('Page 2 of 3')
            ->set('status', 'failed')
            ->assertSet('paginators.page', 1)
            ->assertSee('5 deployments')
            ->assertSee('notification-svc')
            ->assertDontSee('auth-service')
            ->set('search', 'd9a0b73')
            ->assertSee('1 deployment')
            ->call('clearFilters')
            ->assertSee('21 deployments');
    }

    public function test_incident_details_follow_url_scope_and_severity_filter(): void
    {
        $id = (string) DB::table('incidents')->where('severity', 'critical')->value('id');

        Livewire::withQueryParams(['env' => 'production', 'record' => $id, 'severity' => 'critical'])
            ->test(IncidentsIndex::class)
            ->assertSee('1 incident')
            ->assertSee('The primary database is refusing new connections.')
            ->call('closeRecord')
            ->assertDontSee('The primary database is refusing new connections.')
            ->set('environment', 'staging')
            ->call('openRecord', $id)
            ->assertSee('No incidents match these filters.')
            ->assertDontSee('The primary database is refusing new connections.');
    }

    public function test_authenticated_records_and_search_are_limited_to_granted_environments(): void
    {
        config(['signal.demo' => false]);
        $this->get('/deployments')->assertRedirect(route('login'));

        $user = User::factory()->create();
        $staging = DB::table('environments')->where('slug', 'staging')->value('id');
        DB::table('environment_user')->insert(['environment_id' => $staging, 'user_id' => $user->id]);
        $productionService = (string) DB::table('monitored_services')->where('name', 'db-primary')->value('id');
        $productionDeployment = (string) DB::table('deployments')->where('commit', 'a3f8c21')->value('id');

        $this->actingAs($user)->get('/?env=production')
            ->assertOk()
            ->assertSee('7 monitored services')
            ->assertDontSee('DB connection pool exhausted');

        Livewire::test(InfrastructureOverview::class)
            ->call('openService', $productionService)
            ->assertDontSee('db-primary');

        Livewire::test(DeploymentsIndex::class)
            ->call('openRecord', $productionDeployment)
            ->assertDontSee('a3f8c21');

        Livewire::test(GlobalSearch::class)
            ->set('query', 'api-gateway')
            ->assertSee('No matching results.')
            ->set('query', 'preview-gateway')
            ->assertSee('preview-gateway');
    }

    public function test_records_report_data_source_failures(): void
    {
        Schema::drop('deployments');

        $this->get('/deployments')
            ->assertOk()
            ->assertSee('Records are temporarily unavailable.')
            ->assertSee('Try again');
    }

    public function test_access_revocation_removes_previously_loaded_snapshot_data(): void
    {
        config(['signal.demo' => false]);
        $user = User::factory()->create();
        $production = DB::table('environments')->where('slug', 'production')->value('id');
        DB::table('environment_user')->insert(['environment_id' => $production, 'user_id' => $user->id]);
        $this->actingAs($user);
        $component = Livewire::test(InfrastructureOverview::class)->assertSee('DB connection pool exhausted');

        DB::table('environment_user')->where('user_id', $user->id)->delete();

        $component->call('closeService')->assertDontSee('DB connection pool exhausted')
            ->assertSet('snapshot.services', []);
    }
}
