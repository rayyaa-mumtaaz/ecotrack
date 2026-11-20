<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 4: memastikan setiap layout (guest, admin, user) dapat dirender.
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

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan EcoTrack')
            ->assertSee('12,76 kg')            // total berat dari seeder
            ->assertSee('DEP-20261118-0001')   // setoran terbaru
            ->assertSee('Terkumpul');          // label status
    }

    public function test_admin_dashboard_shows_empty_state_without_data(): void
    {
        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Belum ada setoran');
    }

    public function test_user_page_uses_user_layout(): void
    {
        $this->get('/my-deposits')
            ->assertOk()
            ->assertSee('Riwayat Setoran')
            ->assertSee('Keterangan Status');
    }
}
