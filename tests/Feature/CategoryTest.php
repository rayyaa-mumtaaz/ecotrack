<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stage 5 — Modul 2: CRUD kategori.
 */
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
    }

    public function test_admin_can_see_categories(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Perangkat komputer');
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Peralatan Dapur Elektrik', 'description' => 'Blender, rice cooker', 'is_active' => '1'])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['name' => 'Peralatan Dapur Elektrik', 'is_active' => true]);
    }

    public function test_category_name_is_required_and_unique_with_indonesian_message(): void
    {
        Category::create(['name' => 'Baterai', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => 'Baterai'])
            ->assertSessionHasErrors(['name' => 'nama sudah digunakan.']);

        $this->actingAs($this->admin())
            ->post('/admin/categories', ['name' => ''])
            ->assertSessionHasErrors(['name' => 'nama wajib diisi.']);
    }

    public function test_admin_can_update_category_keeping_same_name(): void
    {
        $category = Category::create(['name' => 'Baterai', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->put("/admin/categories/{$category->id}", ['name' => 'Baterai', 'description' => 'Baru'])
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertSame('Baru', $category->description);
        $this->assertFalse($category->is_active); // checkbox tidak dicentang
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $this->seed();
        $category = Category::where('name', 'Perangkat komputer')->first();

        $this->actingAs(User::where('email', 'superadmin@ecotrack.com')->first())
            ->delete("/admin/categories/{$category->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Kosong', 'is_active' => true]);

        $this->actingAs($this->admin())->delete("/admin/categories/{$category->id}");

        $this->assertModelMissing($category);
    }

    public function test_user_sees_only_active_categories(): void
    {
        Category::create(['name' => 'Kategori Aktif', 'is_active' => true]);
        Category::create(['name' => 'Kategori Nonaktif', 'is_active' => false]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get('/categories')
            ->assertOk()
            ->assertSee('Kategori Aktif')
            ->assertDontSee('Kategori Nonaktif');

        $this->actingAs($user)->get('/admin/categories')->assertForbidden();
    }
}
