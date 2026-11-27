<?php

namespace Tests\Feature;

use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Stage 5 — Modul 1: CRUD pengguna + penugasan petugas ke drop point.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    }

    public function test_admin_can_see_users_with_drop_point(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Petugas Lapangan STS')
            ->assertSee('Drop Point Lapangan STS')
            ->assertSee('rayyaa@ecotrack.com');
    }

    public function test_admin_can_create_user_with_hashed_password(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Penyetor Baru',
                'email' => 'baru@ecotrack.com',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'role' => User::ROLE_USER,
            ])
            ->assertRedirect('/admin/users');

        $user = User::where('email', 'baru@ecotrack.com')->first();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertNull($user->drop_point_id);
    }

    public function test_admin_officer_can_be_assigned_to_drop_point(): void
    {
        $dropPoint = DropPoint::create(['name' => 'DP', 'address' => 'Jember', 'is_active' => true]);

        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'Petugas Baru',
            'email' => 'petugas2@ecotrack.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => User::ROLE_ADMIN,
            'drop_point_id' => $dropPoint->id,
        ]);

        $this->assertSame(['Petugas Baru'], $dropPoint->officers()->pluck('name')->all());
    }

    public function test_drop_point_is_cleared_for_role_user(): void
    {
        $dropPoint = DropPoint::create(['name' => 'DP', 'address' => 'Jember', 'is_active' => true]);

        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'Penyetor',
            'email' => 'p@ecotrack.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => User::ROLE_USER,
            'drop_point_id' => $dropPoint->id,
        ]);

        $this->assertNull(User::where('email', 'p@ecotrack.com')->first()->drop_point_id);
    }

    public function test_validation_errors(): void
    {
        $existing = User::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => '',
                'email' => $existing->email,
                'password' => 'pendek',
                'password_confirmation' => 'beda',
                'role' => 'kepala', // role tidak dikenal
            ])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_update_without_password_keeps_old_password(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $oldHash = $user->password;

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}", ['name' => 'Nama Baru', 'email' => $user->email, 'role' => User::ROLE_USER])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame($oldHash, $user->password);
    }

    public function test_admin_cannot_demote_or_delete_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => User::ROLE_USER])
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');

        $this->assertSame(User::ROLE_SUPERADMIN, $admin->fresh()->role);
    }

    public function test_user_with_deposits_cannot_be_deleted(): void
    {
        $this->seed();
        $admin = User::where('email', 'superadmin@ecotrack.com')->first();
        $penyetor = User::where('email', 'rayyaa@ecotrack.com')->first();
        $petugas = User::where('email', 'petugas@ecotrack.com')->first();

        $this->actingAs($admin)->delete("/admin/users/{$penyetor->id}")->assertSessionHas('error');
        $this->actingAs($admin)->delete("/admin/users/{$petugas->id}")->assertSessionHas('error'); // penerima setoran

        $this->assertModelExists($penyetor);
        $this->assertModelExists($petugas);
    }

    public function test_unused_user_can_be_deleted(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($this->admin())->delete("/admin/users/{$user->id}");

        $this->assertModelMissing($user);
    }
}
