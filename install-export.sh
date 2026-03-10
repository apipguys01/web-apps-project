#!/bin/bash
# install-export.sh
# Jalankan sekali di dalam folder project Laravel kamu

echo "📦 Installing export packages..."

# 1. Install dompdf (PDF)
composer require barryvdh/laravel-dompdf

# 2. Install phpspreadsheet (Excel) — langsung, tanpa maatwebsite
composer require phpoffice/phpspreadsheet

echo ""
echo "✅ Done! Sekarang publish config dompdf:"
echo ""

# 3. Publish config dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

echo ""
echo "✅ Semua selesai. Export PDF & Excel siap digunakan."
echo ""
echo "Coba di browser:"
echo "  → /reports?export=pdf"
echo "  → /reports?export=excel"
