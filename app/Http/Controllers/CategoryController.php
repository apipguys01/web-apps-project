<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->where('store_id', Auth::user()->store_id)
            ->orderBy('name')
            ->get();

        return view('store.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        Category::create([
            'store_id' => Auth::user()->store_id,
            'name'     => $request->name,
            'icon'     => $request->icon ?? '📦',
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        if ($category->store_id !== Auth::user()->store_id) abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $category->update($request->only('name', 'icon'));
        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->store_id !== Auth::user()->store_id) abort(403);

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
