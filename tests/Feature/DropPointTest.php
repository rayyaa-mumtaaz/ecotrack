<?php

namespace Tests\Feature;

use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 5 — Modul 3: CRUD drop point, peta, dan data peta (JSON).
 */
class DropPointTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function penyetor(): User
    {
        return User::factory()->create(['role' => User::ROLE_USER]);
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'Drop Point Fakultas Teknik',
            'address' => 'Lobi Fakultas Teknik',
            'latitude' => '-8.1650000',
            'longitude' => '113.7170000',
            'description' => 'Dekat parkiran.',
            'is_active' => '1',
        ], $override);
    }

    public function test_admin_can_see_drop_point_list(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();

        $this->actingAs($admin)
            ->get('/admin/drop-points')
            ->assertOk()
            ->assertSee('Drop Point Gedung Utama')
            ->assertSee('Admin EcoTrack')        // petugas tampil
            ->assertSee('drop-point-map', false); // peta terpasang
    }

    public function test_admin_can_search_drop_point(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();

        $this->actingAs($admin)
            ->get('/admin/drop-points?search=Perpustakaan')
            ->assertOk()
            ->assertSee('Drop Point Perpustakaan')
            ->assertDontSee('Drop Point Kantin');
    }

    public function test_admin_can_create_drop_point_with_coordinates(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/drop-points', $this->validData());

        $dropPoint = DropPoint::where('name', 'Drop Point Fakultas Teknik')->first();
        $response->assertRedirect("/admin/drop-points/{$dropPoint->id}");

        $this->assertSame(-8.165, $dropPoint->latitude);
        $this->assertSame(113.717, $dropPoint->longitude);
        $this->assertTrue($dropPoint->is_active);
    }

    public function test_drop_point_can_be_saved_without_coordinates(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/drop-points', $this->validData(['latitude' => '', 'longitude' => '']))
            ->assertSessionHasNoErrors();

        $this->assertFalse(DropPoint::first()->hasCoordinates());
    }

    public function test_unchecked_checkbox_makes_drop_point_inactive(): void
    {
        $data = $this->validData();
        unset($data['is_active']);

        $this->actingAs($this->admin())->post('/admin/drop-points', $data);

        $this->assertFalse(DropPoint::first()->is_active);
    }

    public function test_validation_errors(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/drop-points', $this->validData([
                'name' => '',
                'address' => '',
                'latitude' => '120',   // di luar -90..90
                'longitude' => 'abc',
            ]))
            ->assertSessionHasErrors(['name', 'address', 'latitude', 'longitude']);

        $this->assertSame(0, DropPoint::count());
    }

    public function test_latitude_requires_longitude(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/drop-points', $this->validData(['longitude' => '']))
            ->assertSessionHasErrors('longitude');
    }

    public function test_admin_can_update_drop_point(): void
    {
        $dropPoint = DropPoint::create($this->validData(['is_active' => true]));

        $this->actingAs($this->admin())
            ->put("/admin/drop-points/{$dropPoint->id}", $this->validData(['name' => 'Nama Baru', 'latitude' => '-8.17', 'longitude' => '113.72']))
            ->assertRedirect("/admin/drop-points/{$dropPoint->id}");

        $dropPoint->refresh();
        $this->assertSame('Nama Baru', $dropPoint->name);
        $this->assertSame(-8.17, $dropPoint->latitude);
    }

    public function test_admin_can_see_detail_with_officers_and_receiver(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();
        $dropPoint = DropPoint::where('name', 'Drop Point Perpustakaan')->first();

        $this->actingAs($admin)
            ->get("/admin/drop-points/{$dropPoint->id}")
            ->assertOk()
            ->assertSee('Petugas Perpustakaan')   // petugas bertugas & penerima setoran
            ->assertSee('DEP-20261112-0001')
            ->assertSee('drop-point-picker', false);
    }

    public function test_drop_point_without_deposits_can_be_deleted(): void
    {
        $dropPoint = DropPoint::create($this->validData(['is_active' => true]));

        $this->actingAs($this->admin())
            ->delete("/admin/drop-points/{$dropPoint->id}")
            ->assertRedirect('/admin/drop-points');

        $this->assertModelMissing($dropPoint);
    }

    public function test_drop_point_with_deposits_cannot_be_deleted(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();
        $dropPoint = DropPoint::where('name', 'Drop Point Gedung Utama')->first();

        $this->actingAs($admin)
            ->from("/admin/drop-points/{$dropPoint->id}")
            ->delete("/admin/drop-points/{$dropPoint->id}")
            ->assertRedirect("/admin/drop-points/{$dropPoint->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($dropPoint);
    }

    public function test_user_cannot_manage_drop_points(): void
    {
        $user = $this->penyetor();

        $this->actingAs($user)->get('/admin/drop-points')->assertForbidden();
        $this->actingAs($user)->post('/admin/drop-points', $this->validData())->assertForbidden();
        $this->assertSame(0, DropPoint::count());
    }

    public function test_user_can_open_map_page(): void
    {
        $this->actingAs($this->penyetor())
            ->get('/drop-points')
            ->assertOk()
            ->assertSee('Peta Drop Point')
            ->assertSee('Lokasi saya');
    }

    public function test_map_data_for_user_only_contains_active_drop_points(): void
    {
        $this->seed();
        $user = User::where('email', 'rayyaa@ecotrack.test')->first();

        $response = $this->actingAs($user)->getJson('/drop-points/map-data')->assertOk();

        $names = collect($response->json('drop_points'))->pluck('name');
        $this->assertContains('Drop Point Gedung Utama', $names);
        $this->assertNotContains('Drop Point Kantin', $names); // nonaktif
    }

    public function test_map_data_contains_officers_and_collected_items(): void
    {
        $this->seed();
        $user = User::where('email', 'rayyaa@ecotrack.test')->first();

        $response = $this->actingAs($user)->getJson('/drop-points/map-data');
        $perpustakaan = collect($response->json('drop_points'))->firstWhere('name', 'Drop Point Perpustakaan');

        // Seeder: di Perpustakaan ada 1 setoran berstatus Terkumpul (DEP-20261112-0001, 1,26 kg)
        $this->assertSame(['Petugas Perpustakaan'], $perpustakaan['officers']);
        $this->assertSame(1, $perpustakaan['collected_count']);
        $this->assertEquals(1.26, $perpustakaan['collected_weight']);
        $this->assertEquals(-8.1641, $perpustakaan['latitude']);
    }

    public function test_map_data_for_admin_includes_inactive_drop_points(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();

        $names = collect($this->actingAs($admin)->getJson('/drop-points/map-data')->json('drop_points'))->pluck('name');

        $this->assertContains('Drop Point Kantin', $names);
    }

    public function test_map_data_requires_login(): void
    {
        $this->getJson('/drop-points/map-data')->assertUnauthorized();
    }

    public function test_background_map_refresh_does_not_extend_session(): void
    {
        config(['session.idle_timeout' => 15]);
        $user = $this->penyetor();

        $this->actingAs($user)->get('/drop-points')->assertOk();

        // Peta diperbarui otomatis di menit ke-10, tapi pengguna tidak beraktivitas
        $this->travel(10)->minutes();
        $this->getJson('/drop-points/map-data', ['X-Background-Request' => '1'])->assertOk();

        // Menit ke-16 sejak aktivitas terakhir: tetap logout
        $this->travel(6)->minutes();
        $this->getJson('/drop-points/map-data', ['X-Background-Request' => '1'])->assertUnauthorized();
        $this->assertGuest();
    }
}
