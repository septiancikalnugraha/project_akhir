@extends('layouts.app')

@section('title', 'Ekspor Data')

@section('header', 'Ekspor Data')

@section('content')
<div class="export-container">
    <div class="export-section">
        <h3>Ekspor Data Anggota</h3>
        <form action="{{ route('exports.customers') }}" method="GET" class="export-form">
            <div class="form-group">
                <label for="customer-format">Format File</label>
                <select name="format" id="customer-format" class="form-control" required>
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kolom yang Diekspor</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="columns[]" value="code" checked> Kode
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="name" checked> Nama
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="email" checked> Email
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="phone" checked> No. Telepon
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="address" checked> Alamat
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="role" checked> Role
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="status" checked> Status
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="customer-role">Filter Role</label>
                <select name="role" id="customer-role" class="form-control">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="ketua">Ketua</option>
                    <option value="anggota">Anggota</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Ekspor Data Anggota</button>
        </form>
    </div>

    <div class="export-section">
        <h3>Ekspor Data Simpanan</h3>
        <form action="{{ route('exports.deposits') }}" method="GET" class="export-form">
            <div class="form-group">
                <label for="deposit-format">Format File</label>
                <select name="format" id="deposit-format" class="form-control" required>
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kolom yang Diekspor</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="columns[]" value="code" checked> Kode
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="customer_name" checked> Nama Anggota
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="type" checked> Jenis
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="subtotal" checked> Jumlah
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="fee" checked> Biaya Admin
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="total" checked> Total
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="fiscal_date" checked> Tanggal
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="status" checked> Status
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="deposit-type">Filter Jenis</label>
                <select name="type" id="deposit-type" class="form-control">
                    <option value="">Semua Jenis</option>
                    <option value="pokok">Simpanan Pokok</option>
                    <option value="wajib">Simpanan Wajib</option>
                    <option value="sukarela">Simpanan Sukarela</option>
                </select>
            </div>

            <div class="form-group">
                <label for="deposit-date-range">Rentang Tanggal</label>
                <div class="date-range">
                    <input type="date" name="start_date" class="form-control" placeholder="Tanggal Mulai">
                    <input type="date" name="end_date" class="form-control" placeholder="Tanggal Selesai">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Ekspor Data Simpanan</button>
        </form>
    </div>

    <div class="export-section">
        <h3>Ekspor Data Pinjaman</h3>
        <form action="{{ route('exports.loans') }}" method="GET" class="export-form">
            <div class="form-group">
                <label for="loan-format">Format File</label>
                <select name="format" id="loan-format" class="form-control" required>
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Kolom yang Diekspor</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="columns[]" value="code" checked> Kode
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="customer_name" checked> Nama Anggota
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="subtotal" checked> Jumlah Pinjaman
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="fee" checked> Biaya Admin
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="total" checked> Total
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="instalment" checked> Angsuran
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="fiscal_date" checked> Tanggal
                    </label>
                    <label>
                        <input type="checkbox" name="columns[]" value="status" checked> Status
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="loan-status">Filter Status</label>
                <select name="status" id="loan-status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="loan-date-range">Rentang Tanggal</label>
                <div class="date-range">
                    <input type="date" name="start_date" class="form-control" placeholder="Tanggal Mulai">
                    <input type="date" name="end_date" class="form-control" placeholder="Tanggal Selesai">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Ekspor Data Pinjaman</button>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
.export-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 1rem;
}

.export-section {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.export-section h3 {
    margin: 0 0 1.5rem;
    color: #333;
    font-size: 1.2rem;
}

.export-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.date-range {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .export-container {
        grid-template-columns: 1fr;
    }

    .checkbox-group {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
@endsection 