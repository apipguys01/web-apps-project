# 🏪 UMKM Manager

> Platform manajemen toko/warung sederhana — stok, transaksi, laporan, semua dalam satu dashboard.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Fitur

| Fitur | Owner | Kasir |
|-------|:-----:|:-----:|
| Dashboard & statistik real-time | ✅ | ✅ |
| POS / Kasir (AJAX checkout) | ✅ | ✅ |
| Riwayat transaksi | ✅ | ✅ |
| Edit & hapus transaksi | ✅ | ❌ |
| Manajemen produk (CRUD + foto) | ✅ | ❌ |
| Manajemen kategori | ✅ | ❌ |
| Laporan penjualan + grafik | ✅ | ❌ |
| Kelola akun kasir | ✅ | ❌ |
| Pengaturan toko (logo, info) | ✅ | ❌ |

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Database:** MySQL 8
- **Frontend:** Blade, Chart.js, CSS custom (dark mode)
- **Auth:** Custom session-based (tanpa Breeze/Jetstream)

---

## 🚀 Instalasi Lokal

### Prasyarat
- PHP >= 8.2
- Composer
- MySQL

### Langkah-langkah

**1. Clone repo**
```bash
git clone https://github.com/USERNAME/umkm-manager.git
cd umkm-manager
```

**2. Install dependencies**
```bash
composer install
```

**3. Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Konfigurasi database di `.env`**
```env
DB_DATABASE=umkm_manager
DB_USERNAME=root
DB_PASSWORD=your_password
SESSION_DRIVER=file
```

**5. Buat database & jalankan migration**
```bash
mysql -u root -p -e "CREATE DATABASE umkm_manager"
php artisan migrate:fresh --seed
```

**6. Register middleware di `bootstrap/app.php`**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'        => \App\Http\Middleware\CheckRole::class,
        'check.store' => \App\Http\Middleware\CheckStore::class,
    ]);
})
```

**7. Storage link & jalankan server**
```bash
php artisan storage:link
php artisan serve
```

Buka: **http://localhost:8000**

---

## 👤 Akun Demo

| Role | Email | Password |
|------|-------|----------|
| 👑 Owner | owner@warungbudi.com | password |
| 🧑 Kasir | siti@warungbudi.com | password |
| 🧑 Kasir | andi@warungbudi.com | password |

---

## 📁 Struktur Proyek

```
app/Http/Controllers/   → AuthController, DashboardController, ProductController,
                          TransactionController, ReportController,
                          StoreController, CategoryController
app/Http/Middleware/    → CheckRole.php, CheckStore.php
app/Models/             → Store, User, Category, Product, Transaction, TransactionItem
database/migrations/    → 5 migration files
database/seeders/       → DatabaseSeeder.php (data dummy lengkap)
resources/views/        → layouts, auth, dashboard, products,
                          transactions, reports, store
routes/web.php
```

---

## ⚠️ Catatan Penting

- Hapus 3 migration bawaan Laravel sebelum migrate:
  - `0001_01_01_000000_create_users_table.php`
  - `0001_01_01_000001_create_cache_table.php`
  - `0001_01_01_000002_create_jobs_table.php`
- Wajib `SESSION_DRIVER=file` di `.env`

---

## 📄 Lisensi

MIT License

---

## 📤 Setup Export PDF & Excel

Fitur export butuh 2 package tambahan. Jalankan perintah ini di dalam folder project Laravel:

```bash
# Install package PDF
composer require barryvdh/laravel-dompdf

# Install package Excel (phpspreadsheet langsung, lebih ringan)
composer require phpoffice/phpspreadsheet

# Publish config dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

# Clear cache
php artisan optimize:clear
```

Atau jalankan script otomatis yang sudah disediakan:
```bash
chmod +x install-export.sh
./install-export.sh
```

Setelah install, tombol **Export PDF** dan **Export Excel** di halaman Laporan langsung bisa digunakan.

### Troubleshooting Export

| Error | Solusi |
|-------|--------|
| `Class 'Barryvdh\DomPDF\Facade\Pdf' not found` | Jalankan `composer require barryvdh/laravel-dompdf` |
| `Class 'PhpOffice\PhpSpreadsheet\Spreadsheet' not found` | Jalankan `composer require phpoffice/phpspreadsheet` |
| PDF kosong / styling rusak | Pastikan `storage/fonts/` bisa ditulis (`chmod -R 775 storage`) |
| Error 500 saat export | Jalankan `php artisan optimize:clear` |
"# web-apps-project" 
