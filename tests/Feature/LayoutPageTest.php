<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 4: memastikan setiap layout (guest, admin, user) dapat dirender.
 * Sejak Stage 3, halaman admin/user harus diakses setelah login (actingAs).
 */
class LayoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_guest_layout(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke EcoTrack');
    }

    public function test_admin_dashboard_shows_data_from_database(): void
    {
        $this->seed();
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan EcoTrack')
            ->assertSee('12,76 kg')            // total berat dari seeder
            ->assertSee('DEP-20261118-0001')   // setoran terbaru
            ->assertSee('Terkumpul')           // label status
            ->assertSee('idle-warning', false); // peringatan auto-logout terpasang
    }

    public function test_admin_dashboard_shows_empty_state_without_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Belum ada setoran');
    }

    public function test_user_page_uses_user_layout(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get('/my-deposits')
            ->assertOk()
            ->assertSee('Riwayat Setoran')
            ->assertSee('Keterangan Status');
    }
}
