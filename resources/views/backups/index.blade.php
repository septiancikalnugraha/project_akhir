@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Backup Database</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="createBackup">
                            <i class="fas fa-plus"></i> Buat Backup
                        </button>
                        <button type="button" class="btn btn-tool" id="cleanupBackups">
                            <i class="fas fa-broom"></i> Bersihkan Backup Lama
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Nama File</th>
                                    <th width="15%">Ukuran</th>
                                    <th width="20%">Tanggal Dibuat</th>
                                    <th width="30%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $index => $backup)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $backup['filename'] }}</td>
                                    <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                    <td>{{ $backup['created_at']->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('backups.download', $backup['filename']) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning restore-backup" 
                                                data-filename="{{ $backup['filename'] }}">
                                            <i class="fas fa-undo"></i> Pulihkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-backup" 
                                                data-filename="{{ $backup['filename'] }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada backup</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Create backup
    $('#createBackup').click(function() {
        if (confirm('Apakah Anda yakin ingin membuat backup database?')) {
            window.location.href = '{{ route("backups.create") }}';
        }
    });

    // Restore backup
    $('.restore-backup').click(function() {
        var filename = $(this).data('filename');
        
        if (confirm('Apakah Anda yakin ingin memulihkan backup ini? Semua data saat ini akan diganti dengan data dari backup.')) {
            window.location.href = '{{ url("backups") }}/' + filename + '/restore';
        }
    });

    // Delete backup
    $('.delete-backup').click(function() {
        var filename = $(this).data('filename');
        
        if (confirm('Apakah Anda yakin ingin menghapus backup ini?')) {
            window.location.href = '{{ url("backups") }}/' + filename + '/delete';
        }
    });

    // Cleanup old backups
    $('#cleanupBackups').click(function() {
        if (confirm('Apakah Anda yakin ingin membersihkan backup yang lebih lama dari 7 hari?')) {
            window.location.href = '{{ route("backups.cleanup") }}';
        }
    });
});
</script>
@endpush 