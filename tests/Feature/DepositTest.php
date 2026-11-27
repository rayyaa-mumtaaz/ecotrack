<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Deposit;
use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 5 — Modul 4: CRUD setoran (admin) dan riwayat setoran (user).
 */
class DepositTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $penyetor;
    private DropPoint $dropPoint;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Nama dibuat berbeda dari data seeder agar test yang memanggil seed() tidak bentrok (nama kategori unik)
        $this->dropPoint = DropPoint::create(['name' => 'DP Uji', 'address' => 'Jember', 'is_active' => true]);
        $this->category = Category::create(['name' => 'Kategori Uji', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'drop_point_id' => $this->dropPoint->id]);
        $this->penyetor = User::factory()->create(['role' => User::ROLE_USER]);
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'user_id' => $this->penyetor->id,
            'drop_point_id' => $this->dropPoint->id,
            'deposit_date' => now()->toDateString(),
            'notes' => 'Diantar langsung',
            'items' => [
                ['item_name' => 'Laptop', 'category_id' => $this->category->id, 'quantity' => 1, 'weight' => '1.50', 'condition' => 'broken'],
                ['item_name' => 'Mouse', 'category_id' => $this->category->id, 'quantity' => 2, 'weight' => '2.00', 'condition' => 'working', 'notes' => 'Masih bagus'],
            ],
        ], $override);
    }

    public function test_create_form_defaults_to_admin_drop_point(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/deposits/create')
            ->assertOk()
            ->assertSee('item-row-template', false)            // cetakan baris jQuery
            ->assertSee('<option value="' . $this->dropPoint->id . '" selected', false);
    }

    public function test_admin_can_record_deposit_with_items(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/deposits', $this->validData());

        $deposit = Deposit::first();
        $response->assertRedirect("/admin/deposits/{$deposit->id}");

        $this->assertSame(2, $deposit->items()->count());
        $this->assertEquals(3.50, $deposit->total_weight);                   // 1,50 + 2,00
        $this->assertSame($this->admin->id, $deposit->received_by);          // penerima otomatis
        $this->assertSame(Deposit::STATUS_COLLECTED, $deposit->status);
        $this->assertStringStartsWith('DEP-' . now()->format('Ymd') . '-0001', $deposit->deposit_number);
    }

    public function test_received_by_cannot_be_forged_from_form(): void
    {
        $otherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($this->admin)->post('/admin/deposits', $this->validData(['received_by' => $otherAdmin->id]));

        $this->assertSame($this->admin->id, Deposit::first()->received_by);
    }

    public function test_deposit_requires_at_least_one_item(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/deposits', $this->validData(['items' => []]))
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Deposit::count());
    }

    public function test_item_validation(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/deposits', $this->validData(['items' => [
                ['item_name' => '', 'category_id' => 999, 'quantity' => 0, 'weight' => '0', 'condition' => 'hilang'],
            ]]))
            ->assertSessionHasErrors([
                'items.0.item_name', 'items.0.category_id', 'items.0.quantity', 'items.0.weight', 'items.0.condition',
            ]);
    }

    public function test_depositor_must_be_role_user_and_date_not_in_future(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/deposits', $this->validData([
                'user_id' => $this->admin->id,
                'deposit_date' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['user_id', 'deposit_date']);
    }

    public function test_inactive_drop_point_or_category_is_rejected(): void
    {
        $this->dropPoint->update(['is_active' => false]);
        $this->category->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->post('/admin/deposits', $this->validData())
            ->assertSessionHasErrors(['drop_point_id', 'items.0.category_id']);
    }

    public function test_admin_can_update_collected_deposit_and_replace_items(): void
    {
        $this->actingAs($this->admin)->post('/admin/deposits', $this->validData());
        $deposit = Deposit::first();

        $this->actingAs($this->admin)
            ->put("/admin/deposits/{$deposit->id}", $this->validData(['items' => [
                ['item_name' => 'Monitor', 'category_id' => $this->category->id, 'quantity' => 1, 'weight' => '7.25', 'condition' => 'damaged'],
            ]]))
            ->assertRedirect("/admin/deposits/{$deposit->id}");

        $deposit->refresh();
        $this->assertSame(['Monitor'], $deposit->items()->pluck('item_name')->all());
        $this->assertEquals(7.25, $deposit->total_weight);
    }

    public function test_shipped_deposit_cannot_be_edited_or_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/deposits', $this->validData());
        $deposit = Deposit::first();
        $deposit->update(['status' => Deposit::STATUS_SHIPPED]);

        $this->actingAs($this->admin)->get("/admin/deposits/{$deposit->id}/edit")->assertSessionHas('error');
        $this->actingAs($this->admin)->delete("/admin/deposits/{$deposit->id}")->assertSessionHas('error');

        $this->assertModelExists($deposit);
    }

    public function test_collected_deposit_can_be_deleted_with_items(): void
    {
        $this->actingAs($this->admin)->post('/admin/deposits', $this->validData());
        $deposit = Deposit::first();

        $this->actingAs($this->admin)->delete("/admin/deposits/{$deposit->id}")->assertRedirect('/admin/deposits');

        $this->assertModelMissing($deposit);
        $this->assertDatabaseCount('deposit_items', 0);
    }

    public function test_status_only_moves_forward_and_shipped_needs_pickup(): void
    {
        $this->actingAs($this->admin)->post('/admin/deposits', $this->validData());
        $deposit = Deposit::first();

        // collected -> shipped tidak boleh manual (lewat pengangkutan)
        $this->actingAs($this->admin)
            ->patch("/admin/deposits/{$deposit->id}/status", ['status' => Deposit::STATUS_SHIPPED])
            ->assertSessionHas('error');
        $this->assertSame(Deposit::STATUS_COLLECTED, $deposit->fresh()->status);

        // shipped -> completed boleh
        $deposit->update(['status' => Deposit::STATUS_SHIPPED]);
        $this->actingAs($this->admin)
            ->patch("/admin/deposits/{$deposit->id}/status", ['status' => Deposit::STATUS_COMPLETED])
            ->assertSessionHas('success');
        $this->assertSame(Deposit::STATUS_COMPLETED, $deposit->fresh()->status);

        // completed -> collected (mundur) ditolak
        $this->actingAs($this->admin)
            ->patch("/admin/deposits/{$deposit->id}/status", ['status' => Deposit::STATUS_COLLECTED])
            ->assertSessionHas('error');
        $this->assertSame(Deposit::STATUS_COMPLETED, $deposit->fresh()->status);
    }

    public function test_admin_can_filter_deposits(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ecotrack.test')->first();

        $this->actingAs($admin)
            ->get('/admin/deposits?status=completed')
            ->assertOk()
            ->assertSee('DEP-20261020-0001')
            ->assertDontSee('DEP-20261118-0001');

        $this->actingAs($admin)
            ->get('/admin/deposits?search=Mumtaaz')
            ->assertSee('DEP-20261105-0001')
            ->assertDontSee('DEP-20261020-0001');
    }

    public function test_user_sees_only_own_deposits(): void
    {
        $this->seed();
        $rayyaa = User::where('email', 'rayyaa@ecotrack.test')->first();

        $this->actingAs($rayyaa)
            ->get('/my-deposits')
            ->assertOk()
            ->assertSee('DEP-20261020-0001')       // milik Rayyaa
            ->assertDontSee('DEP-20261103-0001')   // milik Tristan
            ->assertSee('3,96 kg');                // 2,70 + 1,26
    }

    public function test_user_can_track_own_deposit_detail(): void
    {
        $this->seed();
        $rayyaa = User::where('email', 'rayyaa@ecotrack.test')->first();
        $deposit = Deposit::where('deposit_number', 'DEP-20261020-0001')->first();

        $this->actingAs($rayyaa)
            ->get("/my-deposits/{$deposit->id}")
            ->assertOk()
            ->assertSee('Laptop bekas')
            ->assertSee('Admin EcoTrack')              // diterima oleh
            ->assertSee('Mitra Daur Ulang Contoh');    // diserahkan ke mitra
    }

    public function test_user_cannot_open_other_users_deposit(): void
    {
        $this->seed();
        $rayyaa = User::where('email', 'rayyaa@ecotrack.test')->first();
        $tristanDeposit = Deposit::where('deposit_number', 'DEP-20261103-0001')->first();

        $this->actingAs($rayyaa)->get("/my-deposits/{$tristanDeposit->id}")->assertForbidden();
        $this->actingAs($rayyaa)->get('/admin/deposits')->assertForbidden();
    }
}
