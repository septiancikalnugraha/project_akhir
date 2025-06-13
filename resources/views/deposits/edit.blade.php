@extends('layouts.app')

@section('title', 'Edit Simpanan')

@section('header', 'Edit Simpanan')

@section('content')
<form action="{{ route('deposits.update', $deposit->id) }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="customer_id">Anggota</label>
        <select name="customer_id" id="customer_id" class="form-control" required>
            <option value="">Pilih Anggota</option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ $deposit->customer_id == $customer->id ? 'selected' : '' }}>
                {{ $customer->name }} ({{ $customer->code }})
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="type">Tipe Simpanan</label>
        <select name="type" id="type" class="form-control" required>
            <option value="">Pilih Tipe</option>
            <option value="pokok" {{ $deposit->type == 'pokok' ? 'selected' : '' }}>Simpanan Pokok</option>
            <option value="wajib" {{ $deposit->type == 'wajib' ? 'selected' : '' }}>Simpanan Wajib</option>
            <option value="sukarela" {{ $deposit->type == 'sukarela' ? 'selected' : '' }}>Simpanan Sukarela</option>
        </select>
    </div>

    <div class="form-group">
        <label for="subtotal">Jumlah</label>
        <input type="number" name="subtotal" id="subtotal" class="form-control" value="{{ $deposit->subtotal }}" required>
    </div>

    <div class="form-group">
        <label for="fee">Biaya Admin</label>
        <input type="number" name="fee" id="fee" class="form-control" value="{{ $deposit->fee }}">
    </div>

    <div class="form-group">
        <label for="total">Total</label>
        <input type="number" name="total" id="total" class="form-control" value="{{ $deposit->total }}" readonly>
    </div>

    <div class="form-group">
        <label for="fiscal_date">Tanggal</label>
        <input type="date" name="fiscal_date" id="fiscal_date" class="form-control" value="{{ date('Y-m-d', strtotime($deposit->fiscal_date)) }}" required>
    </div>

    <div class="form-group">
        <label for="notes">Keterangan</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ $deposit->notes }}</textarea>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('deposits.index') }}" class="btn btn-secondary">Kembali</a>
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
</script>
@endsection 