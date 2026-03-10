<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // ── POS Page ───────────────────────────────────────────────
    public function pos()
    {
        $storeId = Auth::user()->store_id;

        $products = Product::with('category')
            ->forStore($storeId)
            ->active()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $categories = \App\Models\Category::where('store_id', $storeId)
            ->orderBy('name')->get();

        return view('transactions.pos', compact('products', 'categories'));
    }

    // ── Process Transaction ────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
            'paid_amount'          => ['required', 'numeric', 'min:0'],
            'payment_method'       => ['required', 'in:cash,qris,transfer'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $storeId = Auth::user()->store_id;

        DB::beginTransaction();
        try {
            $items       = [];
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Validate store ownership
                if ($product->store_id !== $storeId) abort(403);

                // Check stock
                if ($product->stock < $item['qty']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}",
                    ], 422);
                }

                $subtotal     = $product->price * $item['qty'];
                $totalAmount += $subtotal;

                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'price'        => $product->price,
                    'cost_price'   => $product->cost_price,
                    'qty'          => $item['qty'],
                    'subtotal'     => $subtotal,
                ];

                // Decrement stock
                $product->decrementStock($item['qty']);
            }

            $discount     = $request->discount_amount ?? 0;
            $finalAmount  = $totalAmount - $discount;
            $paidAmount   = $request->paid_amount;
            $changeAmount = max(0, $paidAmount - $finalAmount);

            if ($paidAmount < $finalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah bayar kurang dari total belanja.',
                ], 422);
            }

            // Create transaction
            $transaction = Transaction::create([
                'store_id'        => $storeId,
                'user_id'         => Auth::id(),
                'invoice_no'      => Transaction::generateInvoiceNo($storeId),
                'total_amount'    => $finalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'discount_amount' => $discount,
                'payment_method'  => $request->payment_method,
                'note'            => $request->note,
            ]);

            // Create items
            foreach ($items as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'invoice_no'    => $transaction->invoice_no,
                'total_amount'  => $transaction->total_amount,
                'change_amount' => $transaction->change_amount,
                'message'       => 'Transaksi berhasil!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Transaction History ────────────────────────────────────
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;

        $query = Transaction::with('user')
            ->where('store_id', $storeId)
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('payment')) {
            $query->where('payment_method', $request->payment);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    // ── Show detail (owner only) ───────────────────────────────
    public function show(Transaction $transaction)
    {
        $this->authorizeStore($transaction);
        $transaction->load(['items', 'user']);
        return view('transactions.show', compact('transaction'));
    }

    // ── Edit form (owner only) ─────────────────────────────────
    public function edit(Transaction $transaction)
    {
        $this->authorizeStore($transaction);
        $transaction->load(['items.product']);
        return view('transactions.edit', compact('transaction'));
    }

    // ── Update (owner only) — only note & payment_method ──────
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorizeStore($transaction);

        $request->validate([
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'note'           => ['nullable', 'string', 'max:500'],
            'paid_amount'    => ['required', 'numeric', 'min:0'],
        ]);

        $transaction->update([
            'payment_method' => $request->payment_method,
            'note'           => $request->note,
            'paid_amount'    => $request->paid_amount,
            'change_amount'  => max(0, $request->paid_amount - $transaction->total_amount),
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi ' . $transaction->invoice_no . ' berhasil diperbarui.');
    }

    // ── Delete (owner only) ────────────────────────────────────
    public function destroy(Transaction $transaction)
    {
        $this->authorizeStore($transaction);

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($transaction->items as $item) {
                $item->product?->increment('stock', $item->qty);
            }
            $transaction->items()->delete();
            $transaction->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi ' . $transaction->invoice_no . ' berhasil dihapus. Stok dikembalikan.');
    }

    private function authorizeStore(Transaction $transaction): void
    {
        if ($transaction->store_id !== Auth::user()->store_id) abort(403);
    }

    // ── Get products for POS search (AJAX) ────────────────────
    public function searchProducts(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $search  = $request->get('q', '');

        $products = Product::with('category')
            ->forStore($storeId)
            ->active()
            ->where('stock', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'price', 'stock', 'image', 'category_id']);

        return response()->json($products);
    }
}
