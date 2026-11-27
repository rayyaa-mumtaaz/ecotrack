<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;

/**
 * Modul 2 — Daftar kategori e-waste untuk user (penyetor), hanya yang aktif.
 */
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('user.categories.index', compact('categories'));
    }
}
