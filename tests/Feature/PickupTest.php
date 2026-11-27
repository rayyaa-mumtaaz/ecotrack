<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\Partner;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 6 — Modul 5: pengangkutan setoran ke mitra.
 * Data seeder: DEP-20261112-0001 (1,26 kg) dan DEP-20261118-0001 (0,65 kg) masih Terkumpul.
 */
class PickupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@ecotrack.test')->first();
    }

    private function collectedIds(): array
    {
        return Deposit::where('status', Deposit::STATUS_COLLECTED)->pluck('id')->all();
    }

    private function collector(): Partner
    {
        return Partner::where('type', Partner::TYPE_COLLECTOR)->first();
    }

    public function test_create_form_lists_only_collected_deposits(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/pickups/create')
            ->assertOk()
            ->assertSee('DEP-20261112-0001')
            ->assertSee('DEP-20261118-0001')
            ->assertDontSee('DEP-20261020-0001') // sudah Selesai
            ->assertSee('select-all', false);    // checkbox pilih semua (jQuery)
    }

    public function test_admin_can_record_pickup_and_deposits_become_shipped(): void
    {
        $ids = $this->collectedIds();

        $response = $this->actingAs($this->admin)->post('/admin/pickups', [
            'pickup_date' => now()->toDateString(),
            'partner_id' => $this->collector()->id,
            'notes' => 'Mobil bak terbuka',
            'deposit_ids' => $ids,
        ]);

        $pickup = Pickup::latest('id')->first();
        $response->assertRedirect("/admin/pickups/{$pickup->id}");

        $this->assertEquals(1.91, $pickup->total_weight); // 1,26 + 0,65
        $this->assertStringStartsWith('PCK-' . now()->format('Ymd'), $pickup->pickup_number);
        $this->assertSame(2, Deposit::whereIn('id', $ids)->where('status', Deposit::STATUS_SHIPPED)->where('pickup_id', $pickup->id)->count());
    }

    public function test_pickup_requires_deposits_and_active_partner(): void
    {
        $partner = $this->collector();
        $partner->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->post('/admin/pickups', ['pickup_date' => now()->toDateString(), 'partner_id' => $partner->id, 'deposit_ids' => []])
            ->assertSessionHasErrors(['partner_id', 'deposit_ids']);
    }

    public function test_already_shipped_deposit_cannot_be_picked_up_again(): void
    {
        $shipped = Deposit::where('status', Deposit::STATUS_SHIPPED)->first();

        $this->actingAs($this->admin)
            ->post('/admin/pickups', [
                'pickup_date' => now()->toDateString(),
                'partner_id' => $this->collector()->id,
                'deposit_ids' => [$shipped->id],
            ])
            ->assertSessionHasErrors('deposit_ids.0');

        $this->assertSame(2, Pickup::count()); // tidak bertambah
    }

    public function test_complete_marks_shipped_deposits_as_completed(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261117-0001')->first(); // 2 setoran Diangkut

        $this->actingAs($this->admin)
            ->patch("/admin/pickups/{$pickup->id}/complete")
            ->assertSessionHas('success');

        $this->assertTrue($pickup->isCompleted());
        $this->assertSame(0, $pickup->deposits()->where('status', '!=', Deposit::STATUS_COMPLETED)->count());
    }

    public function test_cancel_pickup_returns_deposits_to_collected(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261117-0001')->first();
        $ids = $pickup->deposits()->pluck('id');

        $this->actingAs($this->admin)
            ->delete("/admin/pickups/{$pickup->id}")
            ->assertRedirect('/admin/pickups');

        $this->assertModelMissing($pickup);
        $this->assertSame(2, Deposit::whereIn('id', $ids)->where('status', Deposit::STATUS_COLLECTED)->whereNull('pickup_id')->count());
    }

    public function test_completed_pickup_cannot_be_cancelled(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261110-0001')->first(); // sudah Selesai

        $this->actingAs($this->admin)->delete("/admin/pickups/{$pickup->id}")->assertSessionHas('error');

        $this->assertModelExists($pickup);
    }

    public function test_admin_can_update_pickup_info(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261117-0001')->first();

        $this->actingAs($this->admin)
            ->put("/admin/pickups/{$pickup->id}", [
                'pickup_date' => '2026-11-18',
                'partner_id' => $this->collector()->id,
                'notes' => 'Diubah',
            ])
            ->assertRedirect("/admin/pickups/{$pickup->id}");

        $this->assertSame($this->collector()->id, $pickup->fresh()->partner_id);
    }

    public function test_index_and_detail_pages(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261117-0001')->first();

        $this->actingAs($this->admin)->get('/admin/pickups')
            ->assertOk()
            ->assertSee('PCK-20261110-0001')
            ->assertSee('menunggu diangkut'); // ada 2 setoran Terkumpul

        $this->actingAs($this->admin)->get("/admin/pickups/{$pickup->id}")
            ->assertOk()
            ->assertSee('Pengrajin Kreasi Contoh')
            ->assertSee('DEP-20261103-0001');
    }

    public function test_user_sees_shipped_status_and_partner_after_completion(): void
    {
        $pickup = Pickup::where('pickup_number', 'PCK-20261117-0001')->first();
        $this->actingAs($this->admin)->patch("/admin/pickups/{$pickup->id}/complete");

        $tristan = User::where('email', 'tristan@ecotrack.test')->first();
        $deposit = Deposit::where('deposit_number', 'DEP-20261103-0001')->first();

        $this->actingAs($tristan)
            ->get("/my-deposits/{$deposit->id}")
            ->assertOk()
            ->assertSee('Diserahkan ke Pengrajin Kreasi Contoh');
    }

    public function test_user_cannot_access_pickups(): void
    {
        $user = User::where('email', 'rayyaa@ecotrack.test')->first();

        $this->actingAs($user)->get('/admin/pickups')->assertForbidden();
    }
}
