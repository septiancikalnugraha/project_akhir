<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Loan;
use App\Models\LoanInstalment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function deposits(Request $request)
    {
        $query = Deposit::with('customer');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->where('fiscal_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('fiscal_date', '<=', $request->end_date);
        }

        $deposits = $query->get();
        $total = $deposits->sum('total');
        $format = $request->input('format', 'pdf');

        $data = [
            'title' => 'Laporan Simpanan',
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date
            ],
            'deposits' => $deposits,
            'total' => $total,
            'type' => $request->type ? ucfirst($request->type) : 'Semua Jenis'
        ];

        if ($format === 'excel') {
            return $this->exportDepositsToExcel($data);
        }

        return $this->exportDepositsToPdf($data);
    }

    public function loans(Request $request)
    {
        $query = Loan::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->where('fiscal_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('fiscal_date', '<=', $request->end_date);
        }

        $loans = $query->get();
        $total = $loans->sum('total');
        $format = $request->input('format', 'pdf');

        $data = [
            'title' => 'Laporan Pinjaman',
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date
            ],
            'loans' => $loans,
            'total' => $total,
            'status' => $request->status ? ucfirst($request->status) : 'Semua Status'
        ];

        if ($format === 'excel') {
            return $this->exportLoansToExcel($data);
        }

        return $this->exportLoansToPdf($data);
    }

    public function instalments(Request $request)
    {
        $query = LoanInstalment::with(['loan.customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('due_date', '<=', $request->end_date);
        }

        $instalments = $query->get();
        $total = $instalments->sum('total');
        $format = $request->input('format', 'pdf');

        $data = [
            'title' => 'Laporan Angsuran',
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date
            ],
            'instalments' => $instalments,
            'total' => $total,
            'status' => $request->status ? ucfirst($request->status) : 'Semua Status'
        ];

        if ($format === 'excel') {
            return $this->exportInstalmentsToExcel($data);
        }

        return $this->exportInstalmentsToPdf($data);
    }

    public function finance(Request $request)
    {
        $date = $request->date;
        $type = $request->type;
        $format = $request->input('format', 'pdf');

        // Get date range based on report type
        switch ($type) {
            case 'daily':
                $startDate = $date;
                $endDate = $date;
                $title = 'Laporan Keuangan Harian';
                break;
            case 'monthly':
                $startDate = date('Y-m-01', strtotime($date));
                $endDate = date('Y-m-t', strtotime($date));
                $title = 'Laporan Keuangan Bulanan';
                break;
            case 'yearly':
                $startDate = date('Y-01-01', strtotime($date));
                $endDate = date('Y-12-31', strtotime($date));
                $title = 'Laporan Keuangan Tahunan';
                break;
        }

        // Get deposits
        $deposits = Deposit::whereBetween('fiscal_date', [$startDate, $endDate])->get();
        $totalDeposits = $deposits->sum('total');

        // Get loans
        $loans = Loan::whereBetween('fiscal_date', [$startDate, $endDate])->get();
        $totalLoans = $loans->sum('total');

        // Get instalments
        $instalments = LoanInstalment::whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->get();
        $totalInstalments = $instalments->sum('total');

        $data = [
            'title' => $title,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'deposits' => [
                'data' => $deposits,
                'total' => $totalDeposits
            ],
            'loans' => [
                'data' => $loans,
                'total' => $totalLoans
            ],
            'instalments' => [
                'data' => $instalments,
                'total' => $totalInstalments
            ],
            'summary' => [
                'total_income' => $totalDeposits + $totalInstalments,
                'total_expense' => $totalLoans,
                'balance' => ($totalDeposits + $totalInstalments) - $totalLoans
            ]
        ];

        if ($format === 'excel') {
            return $this->exportFinanceToExcel($data);
        }

        return $this->exportFinanceToPdf($data);
    }

    private function exportDepositsToExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($data['period']['start'])) . ' - ' . date('d/m/Y', strtotime($data['period']['end'])));
        $sheet->setCellValue('A3', 'Jenis: ' . $data['type']);

        // Set header
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Tanggal');
        $sheet->setCellValue('C5', 'Kode');
        $sheet->setCellValue('D5', 'Nama Anggota');
        $sheet->setCellValue('E5', 'Jenis');
        $sheet->setCellValue('F5', 'Jumlah');
        $sheet->setCellValue('G5', 'Biaya Admin');
        $sheet->setCellValue('H5', 'Total');

        // Set data
        $row = 6;
        foreach ($data['deposits'] as $index => $deposit) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($deposit->fiscal_date)));
            $sheet->setCellValue('C' . $row, $deposit->code);
            $sheet->setCellValue('D' . $row, $deposit->customer->name);
            $sheet->setCellValue('E' . $row, $deposit->type);
            $sheet->setCellValue('F' . $row, number_format($deposit->subtotal, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format($deposit->fee, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, number_format($deposit->total, 0, ',', '.'));
            $row++;
        }

        // Set total
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('H' . $row, number_format($data['total'], 0, ',', '.'));

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $data['title'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file
        $writer->save('php://output');
        exit;
    }

    private function exportDepositsToPdf($data)
    {
        $pdf = PDF::loadView('reports.deposits', $data);
        return $pdf->download($data['title'] . '.pdf');
    }

    private function exportLoansToExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($data['period']['start'])) . ' - ' . date('d/m/Y', strtotime($data['period']['end'])));
        $sheet->setCellValue('A3', 'Status: ' . $data['status']);

        // Set header
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Tanggal');
        $sheet->setCellValue('C5', 'Kode');
        $sheet->setCellValue('D5', 'Nama Anggota');
        $sheet->setCellValue('E5', 'Jumlah Pinjaman');
        $sheet->setCellValue('F5', 'Biaya Admin');
        $sheet->setCellValue('G5', 'Total');
        $sheet->setCellValue('H5', 'Angsuran');
        $sheet->setCellValue('I5', 'Status');

        // Set data
        $row = 6;
        foreach ($data['loans'] as $index => $loan) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($loan->fiscal_date)));
            $sheet->setCellValue('C' . $row, $loan->code);
            $sheet->setCellValue('D' . $row, $loan->customer->name);
            $sheet->setCellValue('E' . $row, number_format($loan->subtotal, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, number_format($loan->fee, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format($loan->total, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, number_format($loan->instalment, 0, ',', '.'));
            $sheet->setCellValue('I' . $row, ucfirst($loan->status));
            $row++;
        }

        // Set total
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('G' . $row, number_format($data['total'], 0, ',', '.'));

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $data['title'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file
        $writer->save('php://output');
        exit;
    }

    private function exportLoansToPdf($data)
    {
        $pdf = PDF::loadView('reports.loans', $data);
        return $pdf->download($data['title'] . '.pdf');
    }

    private function exportInstalmentsToExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($data['period']['start'])) . ' - ' . date('d/m/Y', strtotime($data['period']['end'])));
        $sheet->setCellValue('A3', 'Status: ' . $data['status']);

        // Set header
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Tanggal Jatuh Tempo');
        $sheet->setCellValue('C5', 'Kode Pinjaman');
        $sheet->setCellValue('D5', 'Nama Anggota');
        $sheet->setCellValue('E5', 'Jumlah');
        $sheet->setCellValue('F5', 'Tanggal Bayar');
        $sheet->setCellValue('G5', 'Status');

        // Set data
        $row = 6;
        foreach ($data['instalments'] as $index => $instalment) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($instalment->due_date)));
            $sheet->setCellValue('C' . $row, $instalment->loan->code);
            $sheet->setCellValue('D' . $row, $instalment->loan->customer->name);
            $sheet->setCellValue('E' . $row, number_format($instalment->total, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, $instalment->paid_at ? date('d/m/Y', strtotime($instalment->paid_at)) : '-');
            $sheet->setCellValue('G' . $row, ucfirst($instalment->status));
            $row++;
        }

        // Set total
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('E' . $row, number_format($data['total'], 0, ',', '.'));

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $data['title'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file
        $writer->save('php://output');
        exit;
    }

    private function exportInstalmentsToPdf($data)
    {
        $pdf = PDF::loadView('reports.instalments', $data);
        return $pdf->download($data['title'] . '.pdf');
    }

    private function exportFinanceToExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($data['period']['start'])) . ' - ' . date('d/m/Y', strtotime($data['period']['end'])));

        // Set summary
        $sheet->setCellValue('A4', 'Ringkasan Keuangan');
        $sheet->setCellValue('A5', 'Total Pemasukan');
        $sheet->setCellValue('B5', number_format($data['summary']['total_income'], 0, ',', '.'));
        $sheet->setCellValue('A6', 'Total Pengeluaran');
        $sheet->setCellValue('B6', number_format($data['summary']['total_expense'], 0, ',', '.'));
        $sheet->setCellValue('A7', 'Saldo');
        $sheet->setCellValue('B7', number_format($data['summary']['balance'], 0, ',', '.'));

        // Set deposits
        $sheet->setCellValue('A9', 'Detail Simpanan');
        $sheet->setCellValue('A10', 'No');
        $sheet->setCellValue('B10', 'Tanggal');
        $sheet->setCellValue('C10', 'Kode');
        $sheet->setCellValue('D10', 'Nama Anggota');
        $sheet->setCellValue('E10', 'Jenis');
        $sheet->setCellValue('F10', 'Jumlah');
        $sheet->setCellValue('G10', 'Biaya Admin');
        $sheet->setCellValue('H10', 'Total');

        $row = 11;
        foreach ($data['deposits']['data'] as $index => $deposit) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($deposit->fiscal_date)));
            $sheet->setCellValue('C' . $row, $deposit->code);
            $sheet->setCellValue('D' . $row, $deposit->customer->name);
            $sheet->setCellValue('E' . $row, $deposit->type);
            $sheet->setCellValue('F' . $row, number_format($deposit->subtotal, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format($deposit->fee, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, number_format($deposit->total, 0, ',', '.'));
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'Total Simpanan');
        $sheet->setCellValue('H' . $row, number_format($data['deposits']['total'], 0, ',', '.'));

        // Set loans
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Detail Pinjaman');
        $row++;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal');
        $sheet->setCellValue('C' . $row, 'Kode');
        $sheet->setCellValue('D' . $row, 'Nama Anggota');
        $sheet->setCellValue('E' . $row, 'Jumlah Pinjaman');
        $sheet->setCellValue('F' . $row, 'Biaya Admin');
        $sheet->setCellValue('G' . $row, 'Total');
        $sheet->setCellValue('H' . $row, 'Angsuran');
        $sheet->setCellValue('I' . $row, 'Status');

        $row++;
        foreach ($data['loans']['data'] as $index => $loan) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($loan->fiscal_date)));
            $sheet->setCellValue('C' . $row, $loan->code);
            $sheet->setCellValue('D' . $row, $loan->customer->name);
            $sheet->setCellValue('E' . $row, number_format($loan->subtotal, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, number_format($loan->fee, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format($loan->total, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, number_format($loan->instalment, 0, ',', '.'));
            $sheet->setCellValue('I' . $row, ucfirst($loan->status));
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'Total Pinjaman');
        $sheet->setCellValue('G' . $row, number_format($data['loans']['total'], 0, ',', '.'));

        // Set instalments
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Detail Angsuran');
        $row++;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal Jatuh Tempo');
        $sheet->setCellValue('C' . $row, 'Kode Pinjaman');
        $sheet->setCellValue('D' . $row, 'Nama Anggota');
        $sheet->setCellValue('E' . $row, 'Jumlah');
        $sheet->setCellValue('F' . $row, 'Tanggal Bayar');
        $sheet->setCellValue('G' . $row, 'Status');

        $row++;
        foreach ($data['instalments']['data'] as $index => $instalment) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($instalment->due_date)));
            $sheet->setCellValue('C' . $row, $instalment->loan->code);
            $sheet->setCellValue('D' . $row, $instalment->loan->customer->name);
            $sheet->setCellValue('E' . $row, number_format($instalment->total, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($instalment->paid_at)));
            $sheet->setCellValue('G' . $row, ucfirst($instalment->status));
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'Total Angsuran');
        $sheet->setCellValue('E' . $row, number_format($data['instalments']['total'], 0, ',', '.'));

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $data['title'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file
        $writer->save('php://output');
        exit;
    }

    private function exportFinanceToPdf($data)
    {
        $pdf = PDF::loadView('reports.finance', $data);
        return $pdf->download($data['title'] . '.pdf');
    }
} 