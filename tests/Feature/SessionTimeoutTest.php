<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 3: logout otomatis jika lama tidak aktif (middleware LogoutIfIdle).
 */
class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.idle_timeout' => 15]);
    }

    public function test_user_stays_logged_in_when_active(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)->get('/my-deposits')->assertOk();

        $this->travel(10)->minutes();

        $this->get('/my-deposits')->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_is_logged_out_after_idle_timeout(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)->get('/my-deposits')->assertOk();

        $this->travel(16)->minutes();

        $this->get('/my-deposits')
            ->assertRedirect('/login')
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_ping_extends_the_session(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $this->travel(10)->minutes();
        $this->get('/session/ping')->assertNoContent();

        // 20 menit sejak halaman dibuka, tetapi hanya 10 menit sejak ping
        $this->travel(10)->minutes();
        $this->get('/admin/dashboard')->assertOk();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_guest_cannot_ping(): void
    {
        $this->get('/session/ping')->assertRedirect('/login');
    }
}
