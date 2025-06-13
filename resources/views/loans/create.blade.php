@extends('layouts.app')

@section('title', 'Tambah Pinjaman')

@section('header', 'Tambah Pinjaman')

@section('content')
<form action="{{ route('loans.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="customer_id">Anggota</label>
        <select name="customer_id" id="customer_id" class="form-control" required>
            <option value="">Pilih Anggota</option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="subtotal">Jumlah Pinjaman</label>
        <input type="number" name="subtotal" id="subtotal" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="fee">Biaya Admin</label>
        <input type="number" name="fee" id="fee" class="form-control" value="0">
    </div>

    <div class="form-group">
        <label for="total">Total Pinjaman</label>
        <input type="number" name="total" id="total" class="form-control" readonly>
    </div>

    <div class="form-group">
        <label for="instalment">Angsuran per Bulan</label>
        <input type="number" name="instalment" id="instalment" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="fiscal_date">Tanggal Pinjaman</label>
        <input type="date" name="fiscal_date" id="fiscal_date" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="notes">Keterangan</label>
        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.getElementById('subtotal').addEventListener('input', calculateTotal);
document.getElementById('fee').addEventListener('input', calculateTotal);

function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const fee = parseFloat(document.getElementById('fee').value) || 0;
    const total = subtotal + fee;
    document.getElementById('total').value = total;
}

// Set default date to today
document.getElementById('fiscal_date').valueAsDate = new Date();
</script>
@endsection 