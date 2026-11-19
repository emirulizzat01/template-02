<?php

namespace Tests\Feature;

use App\Livewire\GlobalSearch;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['signal.demo' => true]);
    }

    public function test_branding_is_configurable_on_every_page(): void
    {
        config([
            'app.name' => 'Acme Portal',
            'template.brand.subtitle' => 'Operations',
            'template.description' => 'Team operations workspace.',
        ]);

        foreach (['/', '/deployments', '/incidents', '/form-examples', '/form-examples/monitoring-policy', '/sign-in'] as $path) {
            $this->get($path)->assertOk()->assertSee('Acme Portal')->assertSee('Operations');
        }
    }

    public function test_monitoring_can_be_disabled_while_examples_and_search_remain_available(): void
    {
        config(['template.modules.monitoring' => false]);
        $this->reloadWebRoutes();

        $this->get('/')->assertOk()->assertSee('Explore form examples');
        $this->get('/deployments')->assertNotFound();
        $this->get('/incidents')->assertNotFound();
        $this->get('/form-examples')->assertOk();

        $this->assertArrayNotHasKey('Operations', app(Navigation::class)->groups());
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Form')
            ->assertSee('Form Examples')
            ->assertDontSee('temporarily unavailable');
    }

    public function test_examples_can_be_disabled_independently(): void
    {
        config(['template.modules.examples' => false]);
        $this->reloadWebRoutes();

        $this->get('/form-examples')->assertNotFound();
        $this->get('/form-examples/monitoring-policy')->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('Form Examples');
    }

    public function test_initialization_preserves_an_existing_application_key(): void
    {
        $environmentPath = storage_path('framework/testing/initialization');
        $originalPath = $this->app->environmentPath();
        File::ensureDirectoryExists($environmentPath);
        $fixture = "APP_KEY=existing-test-placeholder\n";
        file_put_contents($environmentPath.'/.env', $fixture);
        $this->app->useEnvironmentPath($environmentPath);

        try {
            $this->artisan('template:initialize')->assertSuccessful();
            $this->assertSame($fixture, file_get_contents($environmentPath.'/.env'));
        } finally {
            $this->app->useEnvironmentPath($originalPath);
            File::deleteDirectory($environmentPath);
        }
    }

    private function reloadWebRoutes(): void
    {
        $routes = new RouteCollection;
        $workspaceRoutes = ['dashboard', 'deployments', 'incidents', 'form-examples.index', 'form-examples.dedicated', 'login', 'login.store', 'logout'];

        foreach (Route::getRoutes() as $route) {
            if (! in_array($route->getName(), $workspaceRoutes, true)) {
                $routes->add($route);
            }
        }

        Route::setRoutes($routes);
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }
}
