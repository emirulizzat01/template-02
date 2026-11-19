<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['signal.demo' => false]);
    }

    public function test_login_and_logout_authenticate_and_clear_the_session(): void
    {
        $user = User::factory()->create();
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/sign-in')->assertOk();

        $this->post('/sign-in', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/sign-out')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_login_does_not_flash_the_password(): void
    {
        $this->post('/sign-in', ['email' => 'missing@example.com', 'password' => 'invalid-password'])
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $this->post('/sign-in', ['email' => 'limited@example.com', 'password' => 'invalid'])
                ->assertRedirect();
        }

        $this->post('/sign-in', ['email' => 'limited@example.com', 'password' => 'invalid'])
            ->assertTooManyRequests();
    }

    public function test_production_never_enables_demo_access(): void
    {
        config(['signal.demo' => true]);
        $this->app->detectEnvironment(fn (): string => 'production');
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/form-examples')->assertRedirect(route('login'));
    }

    public function test_provisioning_requires_an_interactive_password(): void
    {
        $this->artisan('template:user', ['--no-interaction' => true])->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_provisioning_hashes_the_password_and_grants_only_selected_environments(): void
    {
        $production = DB::table('environments')->insertGetId(['slug' => 'production', 'name' => 'Production']);
        DB::table('environments')->insert(['slug' => 'staging', 'name' => 'Staging']);
        $password = str_repeat('fixture-', 3);

        $this->artisan('template:user', ['--environment' => ['production']])
            ->expectsQuestion('Email address', 'operator@example.com')
            ->expectsQuestion('Name', 'Operator')
            ->expectsQuestion('Password (at least 12 characters)', $password)
            ->expectsQuestion('Confirm password', $password)
            ->assertSuccessful();

        $user = User::where('email', 'operator@example.com')->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertSame([$production], DB::table('environment_user')->where('user_id', $user->id)->pluck('environment_id')->all());
    }

    public function test_invalid_environment_grants_do_not_create_partial_users(): void
    {
        $password = str_repeat('fixture-', 3);
        $this->artisan('template:user', ['--environment' => ['missing']])
            ->expectsQuestion('Email address', 'operator@example.com')
            ->expectsQuestion('Name', 'Operator')
            ->expectsQuestion('Password (at least 12 characters)', $password)
            ->expectsQuestion('Confirm password', $password)
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('environment_user', 0);
    }
}
