<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 3: login, logout, dan pembatasan akses berdasarkan role.
 */
class AuthTest extends TestCase
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

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_login_redirects_to_deposit_history(): void
    {
        $user = $this->penyetor();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/my-deposits');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = $this->penyetor();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/my-deposits')->assertRedirect('/login');
    }

    public function test_user_cannot_open_admin_page(): void
    {
        $this->actingAs($this->penyetor())
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSee('Akses ditolak');
    }

    public function test_admin_cannot_open_user_page(): void
    {
        $this->actingAs($this->admin())
            ->get('/my-deposits')
            ->assertForbidden();
    }

    public function test_logged_in_user_opening_login_is_redirected_home(): void
    {
        $this->actingAs($this->admin())
            ->get('/login')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_logout(): void
    {
        $this->actingAs($this->penyetor())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
