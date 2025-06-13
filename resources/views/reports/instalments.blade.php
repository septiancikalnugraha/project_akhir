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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Periode: {{ date('d/m/Y', strtotime($period['start'])) }} - {{ date('d/m/Y', strtotime($period['end'])) }}</p>
        <p>Status: {{ $status }}</p>
    </div>

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
            @foreach($instalments as $index => $instalment)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($instalment->due_date)) }}</td>
                <td>{{ $instalment->loan->code }}</td>
                <td>{{ $instalment->loan->customer->name }}</td>
                <td class="text-right">{{ number_format($instalment->total, 0, ',', '.') }}</td>
                <td class="text-center">{{ $instalment->paid_at ? date('d/m/Y', strtotime($instalment->paid_at)) : '-' }}</td>
                <td class="text-center">{{ ucfirst($instalment->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 0, ',', '.') }}</strong></td>
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