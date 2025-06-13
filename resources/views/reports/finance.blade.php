<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 50%;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($period['start'])) }} - {{ date('d/m/Y', strtotime($period['end'])) }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Total Pemasukan</strong></td>
                <td class="text-right">{{ number_format($summary['total_income'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Pengeluaran</strong></td>
                <td class="text-right">{{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Saldo</strong></td>
                <td class="text-right">{{ number_format($summary['balance'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Detail Simpanan</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th class="text-center" width="10%">Tanggal</th>
                <th class="text-center" width="15%">Kode</th>
                <th width="25%">Nama Anggota</th>
                <th class="text-center" width="10%">Jenis</th>
                <th class="text-right" width="10%">Jumlah</th>
                <th class="text-right" width="10%">Biaya Admin</th>
                <th class="text-right" width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits['data'] as $index => $deposit)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($deposit->fiscal_date)) }}</td>
                <td>{{ $deposit->code }}</td>
                <td>{{ $deposit->customer->name }}</td>
                <td class="text-center">{{ $deposit->type }}</td>
                <td class="text-right">{{ number_format($deposit->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($deposit->fee, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($deposit->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right"><strong>Total Simpanan</strong></td>
                <td class="text-right"><strong>{{ number_format($deposits['total'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Detail Pinjaman</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th class="text-center" width="10%">Tanggal</th>
                <th class="text-center" width="15%">Kode</th>
                <th width="25%">Nama Anggota</th>
                <th class="text-right" width="10%">Jumlah Pinjaman</th>
                <th class="text-right" width="10%">Biaya Admin</th>
                <th class="text-right" width="10%">Total</th>
                <th class="text-right" width="10%">Angsuran</th>
                <th class="text-center" width="5%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans['data'] as $index => $loan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($loan->fiscal_date)) }}</td>
                <td>{{ $loan->code }}</td>
                <td>{{ $loan->customer->name }}</td>
                <td class="text-right">{{ number_format($loan->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($loan->fee, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($loan->total, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($loan->instalment, 0, ',', '.') }}</td>
                <td class="text-center">{{ ucfirst($loan->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Total Pinjaman</strong></td>
                <td class="text-right"><strong>{{ number_format($loans['total'], 0, ',', '.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Detail Angsuran</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th class="text-center" width="15%">Tanggal Jatuh Tempo</th>
                <th class="text-center" width="15%">Kode Pinjaman</th>
                <th width="25%">Nama Anggota</th>
                <th class="text-right" width="15%">Jumlah</th>
                <th class="text-center" width="15%">Tanggal Bayar</th>
                <th class="text-center" width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instalments['data'] as $index => $instalment)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($instalment->due_date)) }}</td>
                <td>{{ $instalment->loan->code }}</td>
                <td>{{ $instalment->loan->customer->name }}</td>
                <td class="text-right">{{ number_format($instalment->total, 0, ',', '.') }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($instalment->paid_at)) }}</td>
                <td class="text-center">{{ ucfirst($instalment->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total Angsuran</strong></td>
                <td class="text-right"><strong>{{ number_format($instalments['total'], 0, ',', '.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>&copy; {{ date('Y') }} Koperasi Simpan Pinjam. All rights reserved.</p>
    </div>
</body>
</html> 