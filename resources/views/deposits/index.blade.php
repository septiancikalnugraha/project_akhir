@extends('layouts.app')

@section('title', 'Daftar Simpanan')

@section('header', 'Daftar Simpanan')

@section('content')
<div class="search-filter">
    <input type="text" id="search" placeholder="Cari simpanan..." class="form-control">
    <select id="filter-type" class="form-control">
        <option value="">Semua Tipe</option>
        <option value="pokok">Simpanan Pokok</option>
        <option value="wajib">Simpanan Wajib</option>
        <option value="sukarela">Simpanan Sukarela</option>
    </select>
    <button class="btn btn-primary" onclick="searchDeposits()">Cari</button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Anggota</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits as $index => $deposit)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $deposit->code }}</td>
                <td>{{ $deposit->customer->name }}</td>
                <td>{{ ucfirst($deposit->type) }}</td>
                <td>Rp {{ number_format($deposit->total, 0, ',', '.') }}</td>
                <td>{{ date('d/m/Y', strtotime($deposit->fiscal_date)) }}</td>
                <td>
                    <span class="status-badge status-{{ $deposit->status }}">
                        {{ ucfirst($deposit->status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('deposits.edit', $deposit->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <button onclick="deleteDeposit({{ $deposit->id }})" class="btn btn-danger btn-sm">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $deposits->links() }}
</div>
@endsection

@section('scripts')
<script>
function searchDeposits() {
    const search = document.getElementById('search').value;
    const type = document.getElementById('filter-type').value;
    window.location.href = `{{ route('deposits.index') }}?search=${search}&type=${type}`;
}

function deleteDeposit(id) {
    if (confirm('Apakah Anda yakin ingin menghapus simpanan ini?')) {
        fetch(`{{ route('deposits.delete', '') }}/${id}`, {
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