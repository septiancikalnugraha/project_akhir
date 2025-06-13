@extends('layouts.app')

@section('title', 'Edit Angsuran')

@section('header', 'Edit Angsuran')

@section('content')
<div class="form-container">
    <form action="{{ route('loans.instalments.update', [$loan->id, $instalment->id]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="due_date">Tanggal Jatuh Tempo</label>
            <input type="date" name="due_date" id="due_date" class="form-control" value="{{ date('Y-m-d', strtotime($instalment->due_date)) }}" required>
        </div>

        <div class="form-group">
            <label for="total">Jumlah Angsuran</label>
            <input type="number" name="total" id="total" class="form-control" value="{{ $instalment->total }}" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control" required>
                <option value="pending" {{ $instalment->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="paid" {{ $instalment->status == 'paid' ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>

        <div class="form-group">
            <label for="paid_at">Tanggal Bayar</label>
            <input type="date" name="paid_at" id="paid_at" class="form-control" value="{{ $instalment->paid_at ? date('Y-m-d', strtotime($instalment->paid_at)) : '' }}">
        </div>

        <div class="form-group">
            <label for="notes">Keterangan</label>
            <textarea name="notes" id="notes" class="form-control" rows="3">{{ $instalment->notes }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('loans.instalments', $loan->id) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@section('scripts')
<script>
    // Show/hide paid_at field based on status
    document.getElementById('status').addEventListener('change', function() {
        const paidAtField = document.getElementById('paid_at');
        if (this.value === 'paid') {
            paidAtField.required = true;
            if (!paidAtField.value) {
                paidAtField.valueAsDate = new Date();
            }
        } else {
            paidAtField.required = false;
            paidAtField.value = '';
        }
    });
</script>
@endsection 