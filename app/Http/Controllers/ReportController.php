<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $period  = $request->get('period', 'today');

        [$startDate, $endDate] = $this->resolvePeriod($period, $request);

        $data = $this->gatherData($storeId, $startDate, $endDate);

        // Export PDF
        if ($request->get('export') === 'pdf') {
            $store = Auth::user()->store;
            $pdf   = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'reports.pdf',
                array_merge($data, compact('period', 'startDate', 'endDate', 'store'))
            );
            $pdf->setPaper('A4', 'portrait');
            $filename = 'laporan-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';
            return $pdf->download($filename);
        }

        // Export Excel
        if ($request->get('export') === 'excel') {
            return $this->exportExcel($data, $startDate, $endDate);
        }

        $transactions = Transaction::with(['user', 'items'])
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.index', array_merge($data, compact(
            'period', 'startDate', 'endDate', 'transactions'
        )));
    }

    public function chartData(Request $request)
    {
        $storeId = Auth::user()->store_id;
        [$startDate, $endDate] = $this->resolvePeriod($request->period ?? 'week', $request);

        $data = Transaction::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    private function gatherData(int $storeId, $startDate, $endDate): array
    {
        $summary = Transaction::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_transactions, SUM(total_amount) as total_revenue, AVG(total_amount) as avg_transaction')
            ->first();

        $grossProfit = TransactionItem::whereHas('transaction', fn($q) =>
                $q->where('store_id', $storeId)->whereBetween('created_at', [$startDate, $endDate])
            )
            ->selectRaw('SUM((price - cost_price) * qty) as profit')
            ->value('profit') ?? 0;

        $salesByDay = Transaction::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')->orderBy('date')->get();

        $topProducts = TransactionItem::whereHas('transaction', fn($q) =>
                $q->where('store_id', $storeId)->whereBetween('created_at', [$startDate, $endDate])
            )
            ->selectRaw('product_name, SUM(qty) as total_qty, SUM(subtotal) as total_revenue, SUM((price - cost_price) * qty) as total_profit')
            ->groupBy('product_name')->orderByDesc('total_qty')->limit(10)->get();

        $paymentMethods = Transaction::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')->get();

        $allTransactions = Transaction::with(['user', 'items'])
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()->get();

        return compact('summary', 'grossProfit', 'salesByDay',
            'topProducts', 'paymentMethods', 'allTransactions');
    }

    private function exportExcel(array $data, $startDate, $endDate)
    {
        $store = Auth::user()->store;
        extract($data);

        $pmMap = ['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank'];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Laporan Penjualan - ' . $store->name)
            ->setCreator($store->name);

        $darkStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1f2e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $stripeStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f4ff']],
        ];
        $borderThin = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'dddddd']]],
        ];
        $rpFormat = '"Rp "#,##0';

        // ── Sheet 1: Ringkasan ────────────────────────────────────
        $s1 = $spreadsheet->getActiveSheet()->setTitle('Ringkasan');

        $s1->setCellValue('A1', 'LAPORAN PENJUALAN - ' . strtoupper($store->name));
        $s1->mergeCells('A1:C1');
        $s1->getStyle('A1')->applyFromArray($darkStyle)->getFont()->setSize(13);
        $s1->getRowDimension(1)->setRowHeight(28);

        $s1->setCellValue('A2', 'Periode: ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'));
        $s1->mergeCells('A2:C2');
        $s1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $s1->setCellValue('A3', 'Dicetak: ' . now()->format('d M Y H:i'));
        $s1->mergeCells('A3:C3');
        $s1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Ringkasan rows
        $s1->setCellValue('A5', 'RINGKASAN');
        $s1->getStyle('A5')->getFont()->setBold(true)->setSize(11);

        $margin = ($summary->total_revenue ?? 0) > 0
            ? round(($grossProfit / $summary->total_revenue) * 100, 1) . '%' : '0%';
        $avgTrx = ($summary->total_transactions ?? 0) > 0
            ? $summary->total_revenue / $summary->total_transactions : 0;

        $rows = [
            6  => ['Total Omzet',         $summary->total_revenue ?? 0, true],
            7  => ['Total Transaksi',      $summary->total_transactions ?? 0, false],
            8  => ['Rata-rata Transaksi',  $avgTrx, true],
            9  => ['Laba Kotor',           $grossProfit, true],
            10 => ['Margin Laba',          $margin, false],
        ];
        foreach ($rows as $r => [$label, $val, $isCurrency]) {
            $s1->setCellValue('A' . $r, $label);
            $s1->setCellValue('B' . $r, $val);
            if ($isCurrency) {
                $s1->getStyle('B' . $r)->getNumberFormat()->setFormatCode($rpFormat);
            }
        }
        $s1->getStyle('A6:B10')->applyFromArray($borderThin);
        $s1->getColumnDimension('A')->setWidth(25);
        $s1->getColumnDimension('B')->setWidth(22);

        // Metode Pembayaran
        $s1->setCellValue('A12', 'METODE PEMBAYARAN');
        $s1->getStyle('A12')->getFont()->setBold(true)->setSize(11);
        $s1->setCellValue('A13', 'Metode');
        $s1->setCellValue('B13', 'Jumlah Transaksi');
        $s1->setCellValue('C13', 'Total');
        $s1->getStyle('A13:C13')->applyFromArray($darkStyle);

        $r = 14;
        foreach ($paymentMethods as $pm) {
            $s1->setCellValue('A' . $r, $pmMap[$pm->payment_method] ?? $pm->payment_method);
            $s1->setCellValue('B' . $r, $pm->count);
            $s1->setCellValue('C' . $r, $pm->total);
            $s1->getStyle('C' . $r)->getNumberFormat()->setFormatCode($rpFormat);
            $r++;
        }
        $s1->getColumnDimension('C')->setWidth(22);

        // Penjualan per hari
        $s1->setCellValue('A' . ($r + 1), 'PENJUALAN PER HARI');
        $s1->getStyle('A' . ($r + 1))->getFont()->setBold(true)->setSize(11);
        $r += 2;
        $s1->setCellValue('A' . $r, 'Tanggal');
        $s1->setCellValue('B' . $r, 'Transaksi');
        $s1->setCellValue('C' . $r, 'Total');
        $s1->getStyle('A' . $r . ':C' . $r)->applyFromArray($darkStyle);
        $r++;
        foreach ($salesByDay as $day) {
            $s1->setCellValue('A' . $r, \Carbon\Carbon::parse($day->date)->format('d M Y'));
            $s1->setCellValue('B' . $r, $day->count);
            $s1->setCellValue('C' . $r, $day->total);
            $s1->getStyle('C' . $r)->getNumberFormat()->setFormatCode($rpFormat);
            $r++;
        }

        // ── Sheet 2: Top Produk ───────────────────────────────────
        $s2 = $spreadsheet->createSheet()->setTitle('Top Produk');

        // Header — pakai array kolom eksplisit, bukan setCellValueByColumnAndRow
        $h2 = ['A' => 'No', 'B' => 'Nama Produk', 'C' => 'Qty Terjual', 'D' => 'Total Omzet', 'E' => 'Total Laba'];
        foreach ($h2 as $col => $label) {
            $s2->setCellValue($col . '1', $label);
        }
        $s2->getStyle('A1:E1')->applyFromArray($darkStyle);

        foreach ($topProducts as $i => $prod) {
            $r = $i + 2;
            $s2->setCellValue('A' . $r, $i + 1);
            $s2->setCellValue('B' . $r, $prod->product_name);
            $s2->setCellValue('C' . $r, $prod->total_qty);
            $s2->setCellValue('D' . $r, $prod->total_revenue);
            $s2->setCellValue('E' . $r, $prod->total_profit ?? 0);
            $s2->getStyle('D' . $r)->getNumberFormat()->setFormatCode($rpFormat);
            $s2->getStyle('E' . $r)->getNumberFormat()->setFormatCode($rpFormat);
            if ($i % 2 === 0) {
                $s2->getStyle("A{$r}:E{$r}")->applyFromArray($stripeStyle);
            }
        }
        $s2->getColumnDimension('B')->setWidth(35);
        $s2->getColumnDimension('D')->setWidth(22);
        $s2->getColumnDimension('E')->setWidth(22);

        // ── Sheet 3: Transaksi ────────────────────────────────────
        $s3 = $spreadsheet->createSheet()->setTitle('Transaksi');

        // Header — pakai array kolom eksplisit
        $h3 = [
            'A' => 'Invoice',   'B' => 'Tanggal',  'C' => 'Waktu',
            'D' => 'Kasir',     'E' => 'Items',    'F' => 'Total',
            'G' => 'Diskon',    'H' => 'Bayar',    'I' => 'Kembalian',
            'J' => 'Laba',      'K' => 'Metode',
        ];
        foreach ($h3 as $col => $label) {
            $s3->setCellValue($col . '1', $label);
        }
        $s3->getStyle('A1:K1')->applyFromArray($darkStyle);

        foreach ($allTransactions as $i => $trx) {
            $r = $i + 2;
            $s3->setCellValue('A' . $r, $trx->invoice_no);
            $s3->setCellValue('B' . $r, $trx->created_at->format('d/m/Y'));
            $s3->setCellValue('C' . $r, $trx->created_at->format('H:i'));
            $s3->setCellValue('D' . $r, $trx->user->name);
            $s3->setCellValue('E' . $r, $trx->items->count());
            $s3->setCellValue('F' . $r, $trx->total_amount);
            $s3->setCellValue('G' . $r, $trx->discount_amount ?? 0);
            $s3->setCellValue('H' . $r, $trx->paid_amount);
            $s3->setCellValue('I' . $r, $trx->change_amount);
            $s3->setCellValue('J' . $r, $trx->total_profit ?? 0);
            $s3->setCellValue('K' . $r, $pmMap[$trx->payment_method] ?? $trx->payment_method);
            foreach (['F', 'G', 'H', 'I', 'J'] as $col) {
                $s3->getStyle($col . $r)->getNumberFormat()->setFormatCode($rpFormat);
            }
            if ($i % 2 === 0) {
                $s3->getStyle("A{$r}:K{$r}")->applyFromArray($stripeStyle);
            }
        }
        foreach (['A' => 18, 'B' => 12, 'C' => 10, 'D' => 20, 'E' => 8, 'F' => 18, 'G' => 15, 'H' => 18, 'I' => 15, 'J' => 18, 'K' => 14] as $col => $width) {
            $s3->getColumnDimension($col)->setWidth($width);
        }

        // Output
        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'laporan-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        return match($period) {
            'today'  => [today()->startOfDay(), today()->endOfDay()],
            'week'   => [now()->startOfWeek(), now()->endOfWeek()],
            'month'  => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay(),
            ],
            default  => [today()->startOfDay(), today()->endOfDay()],
        };
    }
}