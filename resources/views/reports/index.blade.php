@extends('layouts.app')

@section('title', 'Laporan')

@section('header', 'Laporan')

@section('content')
<div class="reports-container">
    <div class="report-section">
        <h3>Laporan Simpanan</h3>
        <form action="{{ route('reports.deposits') }}" method="GET" target="_blank" class="report-form">
            <div class="form-group">
                <label for="deposit-type">Jenis Simpanan</label>
                <select name="type" id="deposit-type" class="form-control">
                    <option value="">Semua Jenis</option>
                    <option value="pokok">Simpanan Pokok</option>
                    <option value="wajib">Simpanan Wajib</option>
                    <option value="sukarela">Simpanan Sukarela</option>
                </select>
            </div>

            <div class="form-group">
                <label for="deposit-date-range">Periode</label>
                <div class="date-range">
                    <input type="date" name="start_date" class="form-control" required>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="deposit-format">Format Laporan</label>
                <select name="format" id="deposit-format" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cetak Laporan Simpanan</button>
        </form>
    </div>

    <div class="report-section">
        <h3>Laporan Pinjaman</h3>
        <form action="{{ route('reports.loans') }}" method="GET" target="_blank" class="report-form">
            <div class="form-group">
                <label for="loan-status">Status Pinjaman</label>
                <select name="status" id="loan-status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="loan-date-range">Periode</label>
                <div class="date-range">
                    <input type="date" name="start_date" class="form-control" required>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="loan-format">Format Laporan</label>
                <select name="format" id="loan-format" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cetak Laporan Pinjaman</button>
        </form>
    </div>

    <div class="report-section">
        <h3>Laporan Angsuran</h3>
        <form action="{{ route('reports.instalments') }}" method="GET" target="_blank" class="report-form">
            <div class="form-group">
                <label for="instalment-status">Status Angsuran</label>
                <select name="status" id="instalment-status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="instalment-date-range">Periode</label>
                <div class="date-range">
                    <input type="date" name="start_date" class="form-control" required>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="instalment-format">Format Laporan</label>
                <select name="format" id="instalment-format" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cetak Laporan Angsuran</button>
        </form>
    </div>

    <div class="report-section">
        <h3>Laporan Keuangan</h3>
        <form action="{{ route('reports.finance') }}" method="GET" target="_blank" class="report-form">
            <div class="form-group">
                <label for="finance-type">Jenis Laporan</label>
                <select name="type" id="finance-type" class="form-control" required>
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="finance-date">Tanggal</label>
                <input type="date" name="date" id="finance-date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="finance-format">Format Laporan</label>
                <select name="format" id="finance-format" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cetak Laporan Keuangan</button>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
.reports-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 1rem;
}

.report-section {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.report-section h3 {
    margin: 0 0 1.5rem;
    color: #333;
    font-size: 1.2rem;
}

.report-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.date-range {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .reports-container {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach((input, index) => {
        if (index % 2 === 0) {
            input.valueAsDate = firstDayOfMonth;
        } else {
            input.valueAsDate = lastDayOfMonth;
        }
    });

    // Set default date for finance report
    document.getElementById('finance-date').valueAsDate = today;
});
</script>
@endsection 