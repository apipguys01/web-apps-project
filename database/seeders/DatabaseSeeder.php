<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Create Store ───────────────────────────────────
        $store = Store::create([
            'name'    => 'Warung Budi',
            'address' => 'Jl. Merdeka No. 12, Jakarta Pusat',
            'phone'   => '081234567890',
            'email'   => 'warungbudi@email.com',
        ]);

        // ── 2. Create Users ───────────────────────────────────
        $owner = User::create([
            'store_id' => $store->id,
            'name'     => 'Budi Wijaya',
            'email'    => 'owner@warungbudi.com',
            'password' => Hash::make('password'),
            'role'     => 'owner',
        ]);

        $kasir1 = User::create([
            'store_id' => $store->id,
            'name'     => 'Siti Rahayu',
            'email'    => 'siti@warungbudi.com',
            'password' => Hash::make('password'),
            'role'     => 'kasir',
        ]);

        $kasir2 = User::create([
            'store_id' => $store->id,
            'name'     => 'Andi Kurniawan',
            'email'    => 'andi@warungbudi.com',
            'password' => Hash::make('password'),
            'role'     => 'kasir',
        ]);

        // ── 3. Create Categories ──────────────────────────────
        $categories = [
            ['name' => 'Minuman',  'icon' => '☕'],
            ['name' => 'Makanan',  'icon' => '🍱'],
            ['name' => 'Snack',    'icon' => '🍿'],
            ['name' => 'Rokok',    'icon' => '🚬'],
            ['name' => 'Lainnya',  'icon' => '📦'],
        ];

        $cats = [];
        foreach ($categories as $cat) {
            $cats[$cat['name']] = Category::create([
                'store_id' => $store->id,
                'name'     => $cat['name'],
                'icon'     => $cat['icon'],
            ]);
        }

        // ── 4. Create Products ────────────────────────────────
        $products = [
            // Minuman
            ['name' => 'Es Teh Manis',    'sku' => 'BVR-001', 'price' => 5000,  'cost' => 2000,  'stock' => 48, 'min' => 10, 'cat' => 'Minuman'],
            ['name' => 'Kopi Hitam',       'sku' => 'BVR-002', 'price' => 8000,  'cost' => 3000,  'stock' => 40, 'min' => 10, 'cat' => 'Minuman'],
            ['name' => 'Aqua 600ml',       'sku' => 'BVR-003', 'price' => 4000,  'cost' => 2500,  'stock' => 55, 'min' => 15, 'cat' => 'Minuman'],
            ['name' => 'Pocari Sweat',     'sku' => 'BVR-004', 'price' => 9500,  'cost' => 7000,  'stock' => 18, 'min' => 10, 'cat' => 'Minuman'],
            ['name' => 'Susu Ultra FC',    'sku' => 'BVR-005', 'price' => 7000,  'cost' => 5000,  'stock' => 22, 'min' => 10, 'cat' => 'Minuman'],
            ['name' => 'Teh Pucuk Harum', 'sku' => 'BVR-006', 'price' => 5000,  'cost' => 3500,  'stock' => 30, 'min' => 10, 'cat' => 'Minuman'],
            // Makanan
            ['name' => 'Nasi Goreng',      'sku' => 'FD-001',  'price' => 18000, 'cost' => 8000,  'stock' => 12, 'min' => 5,  'cat' => 'Makanan'],
            ['name' => 'Mie Goreng',       'sku' => 'FD-002',  'price' => 15000, 'cost' => 6000,  'stock' => 10, 'min' => 5,  'cat' => 'Makanan'],
            ['name' => 'Roti Bakar',       'sku' => 'FD-003',  'price' => 12000, 'cost' => 5000,  'stock' => 8,  'min' => 5,  'cat' => 'Makanan'],
            ['name' => 'Indomie Goreng',   'sku' => 'FD-004',  'price' => 4000,  'cost' => 2500,  'stock' => 30, 'min' => 10, 'cat' => 'Makanan'],
            // Snack
            ['name' => 'Chitato BBQ',      'sku' => 'SNK-001', 'price' => 9000,  'cost' => 6500,  'stock' => 3,  'min' => 5,  'cat' => 'Snack'],
            ['name' => 'Oreo Original',    'sku' => 'SNK-002', 'price' => 6000,  'cost' => 4500,  'stock' => 25, 'min' => 8,  'cat' => 'Snack'],
            ['name' => 'Taro Net',         'sku' => 'SNK-003', 'price' => 4000,  'cost' => 2800,  'stock' => 4,  'min' => 8,  'cat' => 'Snack'],
            // Rokok
            ['name' => 'Gudang Garam 12',  'sku' => 'RKK-001', 'price' => 24000, 'cost' => 21000, 'stock' => 20, 'min' => 5,  'cat' => 'Rokok'],
            ['name' => 'Marlboro Merah',   'sku' => 'RKK-002', 'price' => 35000, 'cost' => 32000, 'stock' => 15, 'min' => 5,  'cat' => 'Rokok'],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $productModels[] = Product::create([
                'store_id'    => $store->id,
                'category_id' => $cats[$p['cat']]->id,
                'name'        => $p['name'],
                'sku'         => $p['sku'],
                'price'       => $p['price'],
                'cost_price'  => $p['cost'],
                'stock'       => $p['stock'],
                'min_stock'   => $p['min'],
            ]);
        }

        // ── 5. Create Sample Transactions (last 7 days) ────────
        $kasirs = [$kasir1, $kasir2];
        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $txCount = rand(5, 15);

            for ($t = 0; $t < $txCount; $t++) {
                $kasir = $kasirs[array_rand($kasirs)];
                $itemCount = rand(1, 4);
                $selectedProducts = collect($productModels)->random($itemCount);

                $items = [];
                $total = 0;

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    $total += $subtotal;
                    $items[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'price'        => $product->price,
                        'cost_price'   => $product->cost_price,
                        'qty'          => $qty,
                        'subtotal'     => $subtotal,
                    ];
                }

                $methods = ['cash', 'qris', 'transfer'];
                $method  = $methods[array_rand($methods)];
                $paid    = ceil($total / 1000) * 1000 + rand(0, 5) * 1000;

                $trx = Transaction::create([
                    'store_id'       => $store->id,
                    'user_id'        => $kasir->id,
                    'invoice_no'     => 'TRX-' . $date->format('Ymd') . '-' . str_pad($t + 1, 4, '0', STR_PAD_LEFT),
                    'total_amount'   => $total,
                    'paid_amount'    => $paid,
                    'change_amount'  => $paid - $total,
                    'payment_method' => $method,
                    'created_at'     => $date->copy()->addHours(rand(7, 20))->addMinutes(rand(0, 59)),
                    'updated_at'     => $date->copy()->addHours(rand(7, 20))->addMinutes(rand(0, 59)),
                ]);

                foreach ($items as $item) {
                    $item['transaction_id'] = $trx->id;
                    TransactionItem::create($item);
                }
            }
        }

        $this->command->info('✅ Seeder selesai!');
        $this->command->info('📧 Login: owner@warungbudi.com / password');
        $this->command->info('📧 Kasir: siti@warungbudi.com / password');
    }
}
