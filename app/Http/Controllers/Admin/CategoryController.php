<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Modul 2 — Category Management (admin).
 */
class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('depositItems')
            ->withSum('depositItems', 'weight')
            ->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create', ['category' => new Category(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $category = Category::create($this->validateCategory($request));

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validateCategory($request, $category));

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Kategori yang sudah dipakai di detail setoran tidak boleh dihapus.
     */
    public function destroy(Category $category)
    {
        if ($category->depositItems()->exists()) {
            return back()->with('error', "Kategori {$category->name} sudah dipakai pada setoran sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} berhasil dihapus.");
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category)],
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
