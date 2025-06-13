@extends('layouts.app')

@section('title', 'Daftar Anggota')

@section('header', 'Daftar Anggota')

@section('content')
<div class="search-filter">
    <input type="text" id="search" placeholder="Cari anggota..." class="form-control" value="{{ request('search') }}">
    <select id="filter-role" class="form-control">
        <option value="">Semua Role</option>
        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="ketua" {{ request('role') == 'ketua' ? 'selected' : '' }}>Ketua</option>
        <option value="anggota" {{ request('role') == 'anggota' ? 'selected' : '' }}>Anggota</option>
    </select>
    <button class="btn btn-primary" onclick="searchCustomers()">Cari</button>
    <a href="{{ route('customers.create') }}" class="btn btn-success">Tambah Anggota</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $index => $customer)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $customer->code }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ ucfirst($customer->role) }}</td>
                <td>
                    <span class="status-badge status-{{ $customer->status }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info btn-sm">Detail</a>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <button onclick="deleteCustomer({{ $customer->id }})" class="btn btn-danger btn-sm">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $customers->links() }}
</div>
@endsection

@section('scripts')
<script>
function searchCustomers() {
    const search = document.getElementById('search').value;
    const role = document.getElementById('filter-role').value;
    window.location.href = `{{ route('customers.index') }}?search=${search}&role=${role}`;
}

function deleteCustomer(id) {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
        fetch(`{{ route('customers.delete', '') }}/${id}`, {
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