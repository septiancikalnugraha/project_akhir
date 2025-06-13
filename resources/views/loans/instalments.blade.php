@extends('layouts.app')

@section('title', 'Angsuran Pinjaman')

@section('header', 'Angsuran Pinjaman')

@section('content')
<div class="loan-info">
    <h3>Informasi Pinjaman</h3>
    <table class="info-table">
        <tr>
            <td>Kode Pinjaman</td>
            <td>: {{ $loan->code }}</td>
        </tr>
        <tr>
            <td>Nama Anggota</td>
            <td>: {{ $loan->customer->name }}</td>
        </tr>
        <tr>
            <td>Total Pinjaman</td>
            <td>: Rp {{ number_format($loan->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Angsuran per Bulan</td>
            <td>: Rp {{ number_format($loan->instalment, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: {{ ucfirst($loan->status) }}</td>
        </tr>
    </table>
</div>

<div class="search-filter">
    <input type="text" id="search" placeholder="Cari angsuran..." class="form-control">
    <select id="filter-status" class="form-control">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="paid">Lunas</option>
    </select>
    <button class="btn btn-primary" onclick="searchInstalments()">Cari</button>
    <a href="{{ route('loans.instalments.create', $loan->id) }}" class="btn btn-success">Tambah Angsuran</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>Jumlah Angsuran</th>
                <th>Tanggal Bayar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instalments as $index => $instalment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($instalment->due_date)) }}</td>
                <td>Rp {{ number_format($instalment->total, 0, ',', '.') }}</td>
                <td>{{ $instalment->paid_at ? date('d/m/Y', strtotime($instalment->paid_at)) : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $instalment->status }}">
                        {{ ucfirst($instalment->status) }}
                    </span>
                </td>
                <td>
                    @if($instalment->status == 'pending')
                    <button onclick="payInstalment({{ $instalment->id }})" class="btn btn-success btn-sm">Bayar</button>
                    @endif
                    <a href="{{ route('loans.instalments.edit', [$loan->id, $instalment->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                    <button onclick="deleteInstalment({{ $instalment->id }})" class="btn btn-danger btn-sm">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $instalments->links() }}
</div>
@endsection

@section('scripts')
<script>
function searchInstalments() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('filter-status').value;
    window.location.href = `{{ route('loans.instalments', $loan->id) }}?search=${search}&status=${status}`;
}

function payInstalment(id) {
    if (confirm('Apakah Anda yakin ingin membayar angsuran ini?')) {
        fetch(`{{ route('loans.instalments.pay', '') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}

function deleteInstalment(id) {
    if (confirm('Apakah Anda yakin ingin menghapus angsuran ini?')) {
        fetch(`{{ route('loans.instalments.delete', '') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}
</script>
@endsection 