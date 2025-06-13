@extends('layouts.app')

@section('title', 'Detail Anggota')

@section('header', 'Detail Anggota')

@section('content')
<div class="customer-detail">
    <div class="detail-section">
        <h3>Informasi Pribadi</h3>
        <table class="info-table">
            <tr>
                <td>Kode Anggota</td>
                <td>: {{ $customer->code }}</td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td>: {{ $customer->name }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $customer->email }}</td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td>: {{ $customer->phone }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $customer->address }}</td>
            </tr>
            <tr>
                <td>Role</td>
                <td>: {{ ucfirst($customer->role) }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: <span class="status-badge status-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td>
            </tr>
            <tr>
                <td>Tanggal Bergabung</td>
                <td>: {{ date('d/m/Y', strtotime($customer->created_at)) }}</td>
            </tr>
        </table>
    </div>

    <div class="detail-section">
        <h3>Ringkasan Keuangan</h3>
        <div class="summary-cards">
            <div class="summary-card">
                <h4>Total Simpanan</h4>
                <p>Rp {{ number_format($totalDeposits, 0, ',', '.') }}</p>
            </div>
            <div class="summary-card">
                <h4>Total Pinjaman</h4>
                <p>Rp {{ number_format($totalLoans, 0, ',', '.') }}</p>
            </div>
            <div class="summary-card">
                <h4>Angsuran Bulan Ini</h4>
                <p>Rp {{ number_format($monthlyInstalments, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>Riwayat Simpanan</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deposits as $index => $deposit)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($deposit->fiscal_date)) }}</td>
                        <td>{{ $deposit->type }}</td>
                        <td>Rp {{ number_format($deposit->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="status-badge status-{{ $deposit->status }}">
                                {{ ucfirst($deposit->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $deposits->links() }}
        </div>
    </div>

    <div class="detail-section">
        <h3>Riwayat Pinjaman</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Angsuran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans as $index => $loan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($loan->fiscal_date)) }}</td>
                        <td>Rp {{ number_format($loan->total, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($loan->instalment, 0, ',', '.') }}</td>
                        <td>
                            <span class="status-badge status-{{ $loan->status }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $loans->links() }}
        </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection 