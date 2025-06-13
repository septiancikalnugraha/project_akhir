@extends('layouts.app')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ \App\Models\User::count() }}</h3>
                                    <p>Total Users</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="{{ route('users.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ \App\Models\Role::count() }}</h3>
                                    <p>Total Roles</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                <a href="{{ route('roles.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ \App\Models\Permission::count() }}</h3>
                                    <p>Total Permissions</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <a href="{{ route('permissions.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ \App\Models\Notification::count() }}</h3>
                                    <p>Total Notifications</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <a href="{{ route('notifications.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Users</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->created_at->diffForHumans() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Notifications</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Models\Notification::latest()->take(5)->get() as $notification)
                                                <tr>
                                                    <td>{{ $notification->title }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $notification->type }}">
                                                            {{ ucfirst($notification->type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Total Anggota</h3>
            <p>{{ $totalCustomers }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>Total Simpanan</h3>
            <p>Rp {{ number_format($totalDeposits, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-info">
            <h3>Total Pinjaman</h3>
            <p>Rp {{ number_format($totalLoans, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>Angsuran Bulan Ini</h3>
            <p>Rp {{ number_format($monthlyInstalments, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

<div class="dashboard-charts">
    <div class="chart-container">
        <h3>Statistik Simpanan</h3>
        <canvas id="depositsChart"></canvas>
    </div>

    <div class="chart-container">
        <h3>Statistik Pinjaman</h3>
        <canvas id="loansChart"></canvas>
    </div>
</div>

<div class="dashboard-tables">
    <div class="table-container">
        <h3>Angsuran Jatuh Tempo</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Pinjaman</th>
                    <th>Nama Anggota</th>
                    <th>Jumlah</th>
                    <th>Jatuh Tempo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dueInstalments as $index => $instalment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $instalment->loan->code }}</td>
                    <td>{{ $instalment->loan->customer->name }}</td>
                    <td>Rp {{ number_format($instalment->total, 0, ',', '.') }}</td>
                    <td>{{ date('d/m/Y', strtotime($instalment->due_date)) }}</td>
                    <td>
                        <button onclick="payInstalment({{ $instalment->id }})" class="btn btn-success btn-sm">Bayar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Simpanan Terbaru</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Anggota</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentDeposits as $index => $deposit)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $deposit->code }}</td>
                    <td>{{ $deposit->customer->name }}</td>
                    <td>{{ $deposit->type }}</td>
                    <td>Rp {{ number_format($deposit->total, 0, ',', '.') }}</td>
                    <td>{{ date('d/m/Y', strtotime($deposit->fiscal_date)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Data untuk grafik simpanan
const depositsData = {
    labels: {!! json_encode($depositsChart['labels']) !!},
    datasets: [{
        label: 'Simpanan',
        data: {!! json_encode($depositsChart['data']) !!},
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

// Data untuk grafik pinjaman
const loansData = {
    labels: {!! json_encode($loansChart['labels']) !!},
    datasets: [{
        label: 'Pinjaman',
        data: {!! json_encode($loansChart['data']) !!},
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
    }]
};

// Konfigurasi grafik
const chartConfig = {
    type: 'line',
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
};

// Render grafik
new Chart(document.getElementById('depositsChart'), {
    ...chartConfig,
    data: depositsData
});

new Chart(document.getElementById('loansChart'), {
    ...chartConfig,
    data: loansData
});

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
</script>
@endsection 