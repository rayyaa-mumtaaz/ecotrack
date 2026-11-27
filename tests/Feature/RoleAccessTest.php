<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Deposit;
use App\Models\DropPoint;
use App\Models\Partner;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 7.1 — Pembagian 3 role:
 * superadmin (koordinator), admin (petugas drop point), user (penyetor).
 *
 * Data: seeder (Drop Point Lapangan STS + Petugas Lapangan STS) ditambah drop point kedua
 * "Drop Point Gedung Kuliah" dengan petugas dan 1 setoran Terkumpul sendiri.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $petugas;        // Lapangan STS
    private User $petugasKuliah;  // Gedung Kuliah
    private DropPoint $kuliah;
    private Deposit $kuliahDeposit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->superadmin = User::where('email', 'superadmin@ecotrack.com')->first();
        $this->petugas = User::where('email', 'petugas@ecotrack.com')->first();

        $this->kuliah = DropPoint::create(['name' => 'Drop Point Gedung Kuliah', 'address' => 'Lobi Gedung Kuliah', 'is_active' => true]);
        $this->petugasKuliah = User::factory()->create(['role' => User::ROLE_ADMIN, 'drop_point_id' => $this->kuliah->id]);

        $this->kuliahDeposit = Deposit::create([
            'deposit_number' => 'DEP-20261120-0099',
            'user_id' => User::where('email', 'rayyaa@ecotrack.com')->value('id'),
            'received_by' => $this->petugasKuliah->id,
            'drop_point_id' => $this->kuliah->id,
            'deposit_date' => '2026-11-20',
            'status' => Deposit::STATUS_COLLECTED,
            'total_weight' => 1.50,
        ]);
    }

    private function depositPayload(array $override = []): array
    {
        return array_merge([
            'user_id' => User::where('email', 'tristan@ecotrack.com')->value('id'),
            'drop_point_id' => $this->kuliah->id,
            'deposit_date' => now()->toDateString(),
            'items' => [[
                'item_name' => 'Keyboard',
                'category_id' => Category::value('id'),
                'quantity' => 1,
                'weight' => 0.5,
                'condition' => 'working',
            ]],
        ], $override);
    }

    // --- Role dan login ---------------------------------------------------

    public function test_seeder_creates_superadmin_and_officer(): void
    {
        $this->assertTrue($this->superadmin->isSuperadmin());
        $this->assertNull($this->superadmin->drop_point_id);
        $this->assertTrue($this->petugas->isAdmin());
        $this->assertTrue($this->petugas->isLimitedToDropPoint());
        $this->assertFalse($this->superadmin->isLimitedToDropPoint());
    }

    public function test_superadmin_and_officer_are_redirected_to_dashboard_after_login(): void
    {
        $this->post('/login', ['email' => 'superadmin@ecotrack.com', 'password' => 'password'])
            ->assertRedirect('/admin/dashboard');

        $this->post('/logout');

        $this->post('/login', ['email' => 'petugas@ecotrack.com', 'password' => 'password'])
            ->assertRedirect('/admin/dashboard');
    }

    public function test_depositor_cannot_open_admin_and_staff_cannot_open_depositor_pages(): void
    {
        $penyetor = User::where('email', 'rayyaa@ecotrack.com')->first();

        $this->actingAs($penyetor)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->superadmin)->get('/my-deposits')->assertForbidden();
        $this->actingAs($this->petugas)->get('/my-deposits')->assertForbidden();
    }

    // --- Master data -------------------------------------------------------

    public function test_officer_can_view_but_not_manage_master_data(): void
    {
        $category = Category::first();
        $partner = Partner::first();

        $this->actingAs($this->petugas)->get('/admin/categories')->assertOk()->assertDontSee('+ Tambah Kategori');
        $this->actingAs($this->petugas)->get('/admin/drop-points')->assertOk()->assertDontSee('+ Tambah Drop Point');
        $this->actingAs($this->petugas)->get("/admin/drop-points/{$this->kuliah->id}")->assertOk();
        $this->actingAs($this->petugas)->get('/admin/partners')->assertOk()->assertDontSee('+ Tambah Mitra');
        $this->actingAs($this->petugas)->get("/admin/partners/{$partner->id}")->assertOk();

        $this->actingAs($this->petugas)->get('/admin/categories/create')->assertForbidden();
        $this->actingAs($this->petugas)->post('/admin/categories', ['name' => 'Baru'])->assertForbidden();
        $this->actingAs($this->petugas)->delete("/admin/categories/{$category->id}")->assertForbidden();
        $this->actingAs($this->petugas)->get('/admin/drop-points/create')->assertForbidden();
        $this->actingAs($this->petugas)->get("/admin/drop-points/{$this->kuliah->id}/edit")->assertForbidden();
        $this->actingAs($this->petugas)->delete("/admin/drop-points/{$this->kuliah->id}")->assertForbidden();
        $this->actingAs($this->petugas)->get('/admin/partners/create')->assertForbidden();
        $this->actingAs($this->petugas)->put("/admin/partners/{$partner->id}", ['name' => 'X'])->assertForbidden();

        $this->assertModelExists($category);
        $this->assertModelExists($this->kuliah);
    }

    public function test_superadmin_can_manage_master_data(): void
    {
        $this->actingAs($this->superadmin)->get('/admin/categories')->assertOk()->assertSee('+ Tambah Kategori');
        $this->actingAs($this->superadmin)->get('/admin/categories/create')->assertOk();
        $this->actingAs($this->superadmin)->get('/admin/drop-points/create')->assertOk();
        $this->actingAs($this->superadmin)->get('/admin/partners/create')->assertOk();
    }

    // --- Pengguna ----------------------------------------------------------

    public function test_officer_only_sees_and_manages_depositor_accounts(): void
    {
        $this->actingAs($this->petugas)->get('/admin/users')
            ->assertOk()
            ->assertSee('rayyaa@ecotrack.com')
            ->assertDontSee('superadmin@ecotrack.com')
            ->assertDontSee($this->petugasKuliah->email);

        $this->actingAs($this->petugas)->get("/admin/users/{$this->superadmin->id}/edit")->assertForbidden();
        $this->actingAs($this->petugas)->put("/admin/users/{$this->petugasKuliah->id}", ['name' => 'X', 'email' => 'x@ecotrack.com'])->assertForbidden();
        $this->actingAs($this->petugas)->delete("/admin/users/{$this->petugasKuliah->id}")->assertForbidden();
    }

    public function test_officer_creates_depositor_even_if_role_admin_is_sent(): void
    {
        $this->actingAs($this->petugas)->post('/admin/users', [
            'name' => 'Penyetor Baru',
            'email' => 'baru@ecotrack.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_SUPERADMIN,
            'drop_point_id' => $this->kuliah->id,
        ])->assertRedirect('/admin/users');

        $user = User::where('email', 'baru@ecotrack.com')->first();
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertNull($user->drop_point_id);
    }

    public function test_superadmin_can_create_officer_with_drop_point(): void
    {
        $this->actingAs($this->superadmin)->post('/admin/users', [
            'name' => 'Petugas Kantin',
            'email' => 'kantin@ecotrack.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
            'drop_point_id' => $this->kuliah->id,
        ])->assertRedirect('/admin/users');

        $user = User::where('email', 'kantin@ecotrack.com')->first();
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertSame($this->kuliah->id, $user->drop_point_id);
    }

    // --- Setoran -----------------------------------------------------------

    public function test_officer_only_sees_deposits_of_own_drop_point(): void
    {
        $this->actingAs($this->petugas)->get('/admin/deposits')
            ->assertOk()
            ->assertSee('DEP-20261118-0001')
            ->assertDontSee('DEP-20261120-0099');

        $this->actingAs($this->superadmin)->get('/admin/deposits')
            ->assertSee('DEP-20261118-0001')
            ->assertSee('DEP-20261120-0099');
    }

    public function test_officer_cannot_touch_deposit_of_other_drop_point(): void
    {
        $id = $this->kuliahDeposit->id;

        $this->actingAs($this->petugas)->get("/admin/deposits/{$id}")->assertForbidden();
        $this->actingAs($this->petugas)->get("/admin/deposits/{$id}/edit")->assertForbidden();
        $this->actingAs($this->petugas)->put("/admin/deposits/{$id}", $this->depositPayload())->assertForbidden();
        $this->actingAs($this->petugas)->patch("/admin/deposits/{$id}/status", ['status' => 'shipped'])->assertForbidden();
        $this->actingAs($this->petugas)->delete("/admin/deposits/{$id}")->assertForbidden();

        $this->assertModelExists($this->kuliahDeposit);
        $this->actingAs($this->petugasKuliah)->get("/admin/deposits/{$id}")->assertOk();
    }

    public function test_officer_deposit_is_always_saved_to_own_drop_point(): void
    {
        // Petugas STS mencoba mencatat ke Gedung Kuliah: tetap tersimpan di Lapangan STS
        $this->actingAs($this->petugas)->post('/admin/deposits', $this->depositPayload())->assertSessionHasNoErrors();

        $deposit = Deposit::latest('id')->first();
        $this->assertSame($this->petugas->drop_point_id, $deposit->drop_point_id);
        $this->assertSame($this->petugas->id, $deposit->received_by);
    }

    public function test_unassigned_officer_cannot_record_deposit(): void
    {
        $unassigned = User::factory()->create(['role' => User::ROLE_ADMIN, 'drop_point_id' => null]);

        $this->actingAs($unassigned)->get('/admin/deposits/create')
            ->assertRedirect('/admin/deposits')
            ->assertSessionHas('error');

        $before = Deposit::count();
        $this->actingAs($unassigned)->post('/admin/deposits', $this->depositPayload())->assertSessionHas('error');
        $this->assertSame($before, Deposit::count());
    }

    // --- Pengangkutan --------------------------------------------------------

    public function test_officer_pickup_form_and_store_only_use_own_deposits(): void
    {
        $this->actingAs($this->petugas)->get('/admin/pickups/create')
            ->assertOk()
            ->assertSee('DEP-20261118-0001')
            ->assertDontSee('DEP-20261120-0099');

        $partner = Partner::first();

        $this->actingAs($this->petugas)->post('/admin/pickups', [
            'pickup_date' => now()->toDateString(),
            'partner_id' => $partner->id,
            'deposit_ids' => [$this->kuliahDeposit->id],
        ])->assertSessionHasErrors('deposit_ids.0');

        $this->assertSame(Deposit::STATUS_COLLECTED, $this->kuliahDeposit->fresh()->status);
    }

    public function test_mixed_pickup_can_be_viewed_but_not_managed_by_officer(): void
    {
        $stsDeposit = Deposit::where('deposit_number', 'DEP-20261118-0001')->first();

        // Superadmin mengangkut setoran dari dua drop point sekaligus
        $this->actingAs($this->superadmin)->post('/admin/pickups', [
            'pickup_date' => now()->toDateString(),
            'partner_id' => Partner::first()->id,
            'deposit_ids' => [$stsDeposit->id, $this->kuliahDeposit->id],
        ])->assertSessionHasNoErrors();

        $pickup = Pickup::latest('id')->first();

        $this->actingAs($this->petugas)->get("/admin/pickups/{$pickup->id}")
            ->assertOk()
            ->assertSee('Perubahan hanya dapat dilakukan superadmin');
        $this->actingAs($this->petugas)->get("/admin/pickups/{$pickup->id}/edit")->assertForbidden();
        $this->actingAs($this->petugas)->patch("/admin/pickups/{$pickup->id}/complete")->assertForbidden();
        $this->actingAs($this->petugas)->delete("/admin/pickups/{$pickup->id}")->assertForbidden();

        $this->actingAs($this->superadmin)->patch("/admin/pickups/{$pickup->id}/complete")->assertSessionHas('success');
    }

    public function test_officer_cannot_open_pickup_without_own_deposits(): void
    {
        $this->actingAs($this->superadmin)->post('/admin/pickups', [
            'pickup_date' => now()->toDateString(),
            'partner_id' => Partner::first()->id,
            'deposit_ids' => [$this->kuliahDeposit->id],
        ]);

        $pickup = Pickup::latest('id')->first();

        $this->actingAs($this->petugas)->get("/admin/pickups/{$pickup->id}")->assertForbidden();
        $this->actingAs($this->petugasKuliah)->get("/admin/pickups/{$pickup->id}")->assertOk();
    }

    // --- Dashboard dan laporan ----------------------------------------------

    public function test_dashboard_and_report_are_limited_for_officer(): void
    {
        // Petugas Gedung Kuliah hanya punya 1 setoran 1,50 kg
        $this->actingAs($this->petugasKuliah)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Drop Point Gedung Kuliah')
            ->assertSee('1,50 kg')
            ->assertDontSee('DEP-20261118-0001');

        $report = $this->actingAs($this->petugasKuliah)->get('/admin/reports')->assertOk();
        $this->assertSame(1, $report->viewData('summary')['deposits']);
        $this->assertEquals(1.50, $report->viewData('summary')['weight']);
        $report->assertSee('Drop Point Gedung Kuliah');

        // Superadmin: 5 setoran seeder + 1 setoran Gedung Kuliah
        $all = $this->actingAs($this->superadmin)->get('/admin/reports')->assertOk()->assertSee('Semua drop point');
        $this->assertSame(6, $all->viewData('summary')['deposits']);
        $this->assertEquals(14.26, $all->viewData('summary')['weight']);
    }

    public function test_officer_can_export_own_report_pdf(): void
    {
        $this->actingAs($this->petugasKuliah)->get('/admin/reports/export/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
