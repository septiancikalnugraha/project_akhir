<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function index()
    {
        return view('exports.index');
    }

    public function exportCustomers(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $customers = $query->get();
        $columns = $request->input('columns', ['code', 'name', 'email', 'phone', 'address', 'role', 'status']);
        $format = $request->input('format', 'xlsx');

        $data = [];
        foreach ($customers as $customer) {
            $row = [];
            foreach ($columns as $column) {
                $row[$column] = $customer->$column;
            }
            $data[] = $row;
        }

        return $this->exportData($data, $columns, 'Data Anggota', $format);
    }

    public function exportDeposits(Request $request)
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
        $columns = $request->input('columns', ['code', 'customer_name', 'type', 'subtotal', 'fee', 'total', 'fiscal_date', 'status']);
        $format = $request->input('format', 'xlsx');

        $data = [];
        foreach ($deposits as $deposit) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'customer_name') {
                    $row[$column] = $deposit->customer->name;
                } else {
                    $row[$column] = $deposit->$column;
                }
            }
            $data[] = $row;
        }

        return $this->exportData($data, $columns, 'Data Simpanan', $format);
    }

    public function exportLoans(Request $request)
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
        $columns = $request->input('columns', ['code', 'customer_name', 'subtotal', 'fee', 'total', 'instalment', 'fiscal_date', 'status']);
        $format = $request->input('format', 'xlsx');

        $data = [];
        foreach ($loans as $loan) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'customer_name') {
                    $row[$column] = $loan->customer->name;
                } else {
                    $row[$column] = $loan->$column;
                }
            }
            $data[] = $row;
        }

        return $this->exportData($data, $columns, 'Data Pinjaman', $format);
    }

    private function exportData($data, $columns, $title, $format)
    {
        if ($format === 'pdf') {
            return $this->exportToPdf($data, $columns, $title);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $col = 1;
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($col, 1, ucfirst(str_replace('_', ' ', $column)));
            $col++;
        }

        // Set data
        $row = 2;
        foreach ($data as $item) {
            $col = 1;
            foreach ($columns as $column) {
                $value = $item[$column];
                if (in_array($column, ['subtotal', 'fee', 'total', 'instalment'])) {
                    $value = number_format($value, 0, ',', '.');
                } elseif (in_array($column, ['fiscal_date'])) {
                    $value = date('d/m/Y', strtotime($value));
                }
                $sheet->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto size columns
        foreach (range(1, count($columns)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // Create writer
        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $filename = $title . '.csv';
        } else {
            $writer = new Xlsx($spreadsheet);
            $filename = $title . '.xlsx';
        }

        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Save file
        $writer->save('php://output');
        exit;
    }

    private function exportToPdf($data, $columns, $title)
    {
        $pdf = PDF::loadView('exports.pdf', [
            'data' => $data,
            'columns' => $columns,
            'title' => $title
        ]);

        return $pdf->download($title . '.pdf');
    }
} 