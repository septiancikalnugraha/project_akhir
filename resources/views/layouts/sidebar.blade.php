<!-- Notifications -->
<li class="nav-item">
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bell"></i>
        <p>
            Notifikasi
            <span class="badge badge-warning right" id="sidebarNotificationCount" style="display: none;">0</span>
        </p>
    </a>
</li>

@push('scripts')
<script>
$(document).ready(function() {
    // Update notification count in sidebar
    function updateSidebarNotificationCount() {
        $.get('{{ route("notifications.unread-count") }}', function(data) {
            $('#sidebarNotificationCount').text(data.count);
            if (data.count === 0) {
                $('#sidebarNotificationCount').hide();
            } else {
                $('#sidebarNotificationCount').show();
            }
        });
    }

    // Update notification count on page load
    updateSidebarNotificationCount();

    // Refresh notification count every minute
    setInterval(updateSidebarNotificationCount, 60000);
});
</script>
@endpush 