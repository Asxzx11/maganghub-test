<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login_before_accessing_the_dashboard_or_actions(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->post('/tools', [
            'name' => 'Multimeter',
            'category' => 'Alat ukur',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('tools', 0);
    }

    public function test_operator_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'petugas@example.com',
            'password' => 'kata-sandi-yang-aman',
        ]);

        $this->post(route('login.store'), [
            'email' => '  PETUGAS@example.com ',
            'password' => 'kata-sandi-yang-aman',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get('/')->assertOk();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_do_not_authenticate_and_show_a_generic_error(): void
    {
        User::factory()->create([
            'email' => 'petugas@example.com',
            'password' => 'kata-sandi-yang-aman',
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'petugas@example.com',
            'password' => 'kata-sandi-salah',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_page_has_no_public_registration_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke Teman Gudang')
            ->assertDontSee('Daftar');
    }

    public function test_login_form_uses_https_when_behind_a_tls_terminating_proxy(): void
    {
        $this->get(route('login'), [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port' => '443',
        ])->assertOk()
            ->assertSee('action="https://localhost/login"', false);
    }

    public function test_operator_account_can_be_created_with_a_hidden_password_prompt(): void
    {
        $this->artisan('users:create', [
            'name' => 'Petugas Shift',
            'email' => 'Petugas@example.com',
        ])->expectsQuestion('Kata sandi (minimal 12 karakter)', 'kata-sandi-yang-aman')
            ->expectsQuestion('Ulangi kata sandi', 'kata-sandi-yang-aman')
            ->assertExitCode(0);

        $user = User::where('email', 'petugas@example.com')->firstOrFail();

        $this->assertTrue(password_verify('kata-sandi-yang-aman', $user->password));
    }

    public function test_database_seeder_creates_operator_from_environment_and_is_idempotent(): void
    {
        config([
            'operator.name' => 'Petugas Gudang',
            'operator.email' => '  PETUGAS@example.com ',
            'operator.password' => 'kata-sandi-awal-yang-aman',
        ]);

        $this->seed();

        $user = User::where('email', 'petugas@example.com')->firstOrFail();
        $this->assertSame('Petugas Gudang', $user->name);
        $this->assertTrue(password_verify('kata-sandi-awal-yang-aman', $user->password));

        config([
            'operator.name' => 'Nama Baru',
            'operator.password' => 'kata-sandi-baru-yang-aman',
        ]);

        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Petugas Gudang', $user->fresh()->name);
        $this->assertTrue(password_verify('kata-sandi-awal-yang-aman', $user->fresh()->password));
    }

    public function test_database_seeder_fails_clearly_when_operator_credentials_are_missing(): void
    {
        config([
            'operator.name' => '',
            'operator.email' => '',
            'operator.password' => '',
        ]);

        try {
            $this->seed();
            $this->fail('Seeding must require operator credentials.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('OPERATOR_NAME', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'petugas@example.com',
                'password' => 'kata-sandi-salah',
            ])->assertRedirect();
        }

        $this->post(route('login.store'), [
            'email' => 'petugas@example.com',
            'password' => 'kata-sandi-salah',
        ])->assertTooManyRequests();
    }
}
