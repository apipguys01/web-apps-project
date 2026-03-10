<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;

        $query = Product::with('category')
            ->forStore($storeId)
            ->orderBy('name');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter low stock
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%");
            });
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::where('store_id', $storeId)->orderBy('name')->get();
        $lowStockCount = Product::forStore($storeId)->lowStock()->count();

        return view('products.index', compact('products', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = Category::where('store_id', Auth::user()->store_id)
            ->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku'         => ['required', 'string', 'max:50', 'unique:products,sku'],
            'price'       => ['required', 'numeric', 'min:0'],
            'cost_price'  => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'min_stock'   => ['required', 'integer', 'min:0'],
            'unit'        => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->except('image');
        $data['store_id'] = Auth::user()->store_id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $request->name . '" berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $this->authorizeStore($product);

        $categories = Category::where('store_id', Auth::user()->store_id)
            ->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeStore($product);

        $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku'         => ['required', 'string', 'max:50', "unique:products,sku,{$product->id}"],
            'price'       => ['required', 'numeric', 'min:0'],
            'cost_price'  => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'min_stock'   => ['required', 'integer', 'min:0'],
            'unit'        => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeStore($product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // Auto-generate SKU via AJAX
    public function generateSku()
    {
        $sku = strtoupper(Str::random(3)) . '-' . rand(100, 999);
        while (Product::where('sku', $sku)->exists()) {
            $sku = strtoupper(Str::random(3)) . '-' . rand(100, 999);
        }
        return response()->json(['sku' => $sku]);
    }

    private function authorizeStore(Product $product): void
    {
        if ($product->store_id !== Auth::user()->store_id) {
            abort(403);
        }
    }
}
