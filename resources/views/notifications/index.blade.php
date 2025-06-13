@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notifikasi</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="markAllAsRead">
                            <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                        <button type="button" class="btn btn-tool text-danger" id="deleteAll">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pesan</th>
                                    <th>Tipe</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->title }}</td>
                                    <td>{{ $notification->message }}</td>
                                    <td>
                                        <span class="badge badge-{{ $notification->type }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info mark-as-read" 
                                                data-id="{{ $notification->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-notification" 
                                                data-id="{{ $notification->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada notifikasi</td>
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
    // Mark as read
    $('.mark-as-read').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.post(`/notifications/${id}/mark-as-read`, {
            _token: '{{ csrf_token() }}'
        })
        .done(function() {
            row.fadeOut();
            updateNotificationCount();
        });
    });

    // Mark all as read
    $('#markAllAsRead').click(function() {
        $.post('/notifications/mark-all-as-read', {
            _token: '{{ csrf_token() }}'
        })
        .done(function() {
            $('tbody tr').fadeOut();
            updateNotificationCount();
        });
    });

    // Delete notification
    $('.delete-notification').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
            $.ajax({
                url: `/notifications/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    row.fadeOut();
                    updateNotificationCount();
                }
            });
        }
    });

    // Delete all notifications
    $('#deleteAll').click(function() {
        if (confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')) {
            $.ajax({
                url: '/notifications/delete-all',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    $('tbody tr').fadeOut();
                    updateNotificationCount();
                }
            });
        }
    });

    // Update notification count
    function updateNotificationCount() {
        $.get('/notifications/unread-count', function(data) {
            $('#notificationCount').text(data.count);
            if (data.count === 0) {
                $('#notificationCount').hide();
            } else {
                $('#notificationCount').show();
            }
        });
    }
});
</script>
@endpush 