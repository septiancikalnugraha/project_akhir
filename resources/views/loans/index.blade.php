@extends('layouts.app')

@section('title', 'Daftar Pinjaman')

@section('header', 'Daftar Pinjaman')

@section('content')
<div class="search-filter">
    <input type="text" id="search" placeholder="Cari pinjaman..." class="form-control">
    <select id="filter-status" class="form-control">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="approved">Disetujui</option>
        <option value="rejected">Ditolak</option>
        <option value="paid">Lunas</option>
    </select>
    <button class="btn btn-primary" onclick="searchLoans()">Cari</button>
    <a href="{{ route('loans.create') }}" class="btn btn-success">Tambah Pinjaman</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Anggota</th>
                <th>Jumlah Pinjaman</th>
                <th>Angsuran</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $loan->code }}</td>
                <td>{{ $loan->customer->name }}</td>
                <td>Rp {{ number_format($loan->total, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($loan->instalment, 0, ',', '.') }}</td>
                <td>{{ date('d/m/Y', strtotime($loan->fiscal_date)) }}</td>
                <td>
                    <span class="status-badge status-{{ $loan->status }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('loans.edit', $loan->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <button onclick="deleteLoan({{ $loan->id }})" class="btn btn-danger btn-sm">Hapus</button>
                    <a href="{{ route('loans.instalments', $loan->id) }}" class="btn btn-info btn-sm">Angsuran</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $loans->links() }}
</div>
@endsection

@section('scripts')
<script>
function searchLoans() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('filter-status').value;
    window.location.href = `{{ route('loans.index') }}?search=${search}&status=${status}`;
}

function deleteLoan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus pinjaman ini?')) {
        fetch(`{{ route('loans.delete', '') }}/${id}`, {
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