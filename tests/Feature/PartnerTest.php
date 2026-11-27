<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 5 — Modul 6: CRUD mitra.
 */
class PartnerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'Kerajinan Kabel Jember',
            'type' => Partner::TYPE_CRAFTSMAN,
            'contact_person' => 'Pak Budi',
            'phone' => '08123456789',
            'email' => 'kerajinan@contoh.test',
            'address' => 'Jember',
            'description' => 'Membuat kerajinan dari kabel bekas',
            'is_active' => '1',
        ], $override);
    }

    public function test_admin_can_filter_partners_by_type(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->get('/admin/partners?type=collector')
            ->assertOk()
            ->assertSee('UD Mitra Rosok Elektronik')
            ->assertDontSee('CV Lestari Daur Elektronik');
    }

    public function test_admin_can_create_partner(): void
    {
        $this->actingAs($this->admin())->post('/admin/partners', $this->validData());

        $partner = Partner::first();
        $this->assertSame('Kerajinan Kabel Jember', $partner->name);
        $this->assertSame('Pengrajin', $partner->typeLabel());
    }

    public function test_invalid_partner_type_and_email_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/partners', $this->validData(['type' => 'bank', 'email' => 'bukan-email', 'address' => '']))
            ->assertSessionHasErrors(['type', 'email', 'address']);

        $this->assertSame(0, Partner::count());
    }

    public function test_partner_detail_shows_pickup_history(): void
    {
        $this->seed();
        $partner = Partner::where('type', Partner::TYPE_RECYCLER)->first();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->get("/admin/partners/{$partner->id}")
            ->assertOk()
            ->assertSee('PCK-20261110-0001');
    }

    public function test_partner_with_pickups_cannot_be_deleted(): void
    {
        $this->seed();
        $partner = Partner::where('type', Partner::TYPE_RECYCLER)->first();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->delete("/admin/partners/{$partner->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($partner);
    }

    public function test_admin_can_update_and_delete_unused_partner(): void
    {
        $admin = $this->admin();
        $partner = Partner::create($this->validData(['is_active' => true]));

        $this->actingAs($admin)
            ->put("/admin/partners/{$partner->id}", $this->validData(['type' => Partner::TYPE_REPAIR]))
            ->assertSessionHasNoErrors();
        $this->assertSame(Partner::TYPE_REPAIR, $partner->fresh()->type);

        $this->actingAs($admin)->delete("/admin/partners/{$partner->id}");
        $this->assertModelMissing($partner);
    }

    public function test_user_cannot_access_partners(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_USER]))
            ->get('/admin/partners')
            ->assertForbidden();
    }
}
