<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $storeId = Auth::user()->store_id;
        $today   = today();

        // ── Today's Stats ──────────────────────────────────────
        $todayRevenue = Transaction::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $todayTransactions = Transaction::where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->count();

        $yesterdayRevenue = Transaction::where('store_id', $storeId)
            ->whereDate('created_at', $today->copy()->subDay())
            ->sum('total_amount');

        $revenueChange = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : 0;

        // ── Products Stats ─────────────────────────────────────
        $totalProducts = Product::forStore($storeId)->active()->count();
        $lowStockCount = Product::forStore($storeId)->active()->lowStock()->count();

        // ── Kasir Count ────────────────────────────────────────
        $kasirCount = User::where('store_id', $storeId)->where('role', 'kasir')->count();

        // ── 7-Day Sales Chart ──────────────────────────────────
        $weeklySales = Transaction::where('store_id', $storeId)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill missing days with 0
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $salesChart[] = [
                'label' => now()->subDays($i)->locale('id')->isoFormat('ddd'),
                'value' => (float) ($weeklySales[$date] ?? 0),
            ];
        }

        // ── Category Revenue Distribution ──────────────────────
        $categoryRevenue = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->join('products as p', 'p.id', '=', 'ti.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('t.store_id', $storeId)
            ->whereMonth('t.created_at', now()->month)
            ->selectRaw('c.name, c.icon, SUM(ti.subtotal) as total')
            ->groupBy('c.id', 'c.name', 'c.icon')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Recent Transactions ────────────────────────────────
        $recentTransactions = Transaction::with('user')
            ->where('store_id', $storeId)
            ->latest()
            ->limit(10)
            ->get();

        // ── Low Stock Products ─────────────────────────────────
        $lowStockProducts = Product::with('category')
            ->forStore($storeId)
            ->active()
            ->lowStock()
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'todayRevenue', 'todayTransactions', 'revenueChange',
            'yesterdayRevenue', 'totalProducts', 'lowStockCount',
            'kasirCount', 'salesChart', 'categoryRevenue',
            'recentTransactions', 'lowStockProducts'
        ));
    }
}
