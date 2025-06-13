<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge" id="notificationCount" style="display: none;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header" id="notificationHeader">0 Notifikasi</span>
                <div class="dropdown-divider"></div>
                <div id="notificationList">
                    <!-- Notifications will be loaded here -->
                </div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">Lihat Semua Notifikasi</a>
            </div>
        </li>
        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">
                    {{ auth()->user()->name }}
                </span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-user-cog mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>

@push('scripts')
<script>
$(document).ready(function() {
    // Load notifications
    function loadNotifications() {
        $.get('{{ route("notifications.unread") }}', function(data) {
            var count = data.length;
            $('#notificationCount').text(count);
            $('#notificationHeader').text(count + ' Notifikasi');
            
            if (count > 0) {
                $('#notificationCount').show();
            } else {
                $('#notificationCount').hide();
            }

            var html = '';
            data.forEach(function(notification) {
                html += `
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-${getNotificationIcon(notification.type)} mr-2"></i> ${notification.title}
                        <span class="float-right text-muted text-sm">${notification.created_at}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                `;
            });

            $('#notificationList').html(html);
        });
    }

    // Get notification icon based on type
    function getNotificationIcon(type) {
        switch(type) {
            case 'success':
                return 'check-circle';
            case 'warning':
                return 'exclamation-triangle';
            case 'error':
                return 'times-circle';
            default:
                return 'info-circle';
        }
    }

    // Load notifications on page load
    loadNotifications();

    // Refresh notifications every minute
    setInterval(loadNotifications, 60000);
});
</script>
@endpush 