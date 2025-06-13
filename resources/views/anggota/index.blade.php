@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="float-left">Daftar Anggota</h4>
                    <a href="{{ route('anggota.create') }}" class="btn btn-primary float-right">Tambah Anggota</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anggotas as $anggota)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $anggota->nama }}</td>
                                    <td>{{ $anggota->alamat }}</td>
                                    <td>{{ $anggota->telepon }}</td>
                                    <td>{{ $anggota->email }}</td>
                                    <td>
                                        <a href="{{ route('anggota.show', $anggota) }}" class="btn btn-info btn-sm">Detail</a>
                                        <a href="{{ route('anggota.edit', $anggota) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('anggota.destroy', $anggota) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $anggotas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 