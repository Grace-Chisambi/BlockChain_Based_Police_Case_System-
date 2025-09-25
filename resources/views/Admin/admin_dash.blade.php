@extends('layouts.admin')

@section('content')
<style>
    .staff-table thead th {
        background-color: #e9f0ff;
        color: #0d6efd;
        font-weight: 600;
    }
    .staff-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .staff-table td, .staff-table th {
        vertical-align: middle;
        padding: 0.75rem;
    }
</style>

<div class="row">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            {{-- Metrics Cards --}}
            @foreach ([
                ['icon' => 'folder-open', 'title' => 'Total Cases', 'count' => $casesCount, 'color' => 'primary', 'change' => '+3% from last month'],
                ['icon' => 'exclamation-circle', 'title' => 'Total Complaints', 'count' => $complaintsCount, 'color' => 'warning', 'change' => '+6% new complaints'],
                ['icon' => 'users', 'title' => 'Users', 'count' => $usersCount, 'color' => 'info', 'change' => '+4% user growth']
            ] as $item)
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-white shadow-sm rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-{{ $item['icon'] }} fa-3x text-{{ $item['color'] }}"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-2">{{ $item['title'] }}</p>
                            <h6 class="mb-0">{{ $item['count'] }}</h6>
                            <small class="text-{{ $item['color'] }}">{{ $item['change'] }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Staff Table --}}
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card bg-white shadow rounded-3 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title text-primary mb-0">Police Staff List</h4>
                    <a href="{{ url('user_management') }}" class="btn btn-primary">View All Staff</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-dark staff-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Available</th>
                                <th>Specialization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffList as $staff)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $staff->sname ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $staff->available ? 'success' : 'secondary' }}">
                                            {{ $staff->available ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $staff->specialization }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No staff records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3 d-flex justify-content-center">
                    {{ $staffList->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        {{-- Logs & Doughnut Chart --}}
        <div class="col-md-4">
            <div class="card bg-white shadow rounded-3 p-4">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Recent Activity</h4>
                    <canvas id="transaction-history" height="200" class="mb-4"></canvas>

                    <div class="transaction-history">
                        @forelse($latestLogs as $log)
                            <div class="log-entry bg-white shadow-sm d-flex justify-content-between align-items-center p-3 rounded mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $log->action_type ?? 'N/A' }}</h6>
                                    <p class="text-muted mb-0">
                                        <small>{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="log-entry bg-white shadow-sm d-flex justify-content-between align-items-center p-3 rounded">
                                <div>
                                    <h6 class="mb-1">No recent activity found.</h6>
                                    <p class="text-muted mb-0">--</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Doughnut Chart Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxDoughnut = document.getElementById('transaction-history').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Cases', 'Complaints', 'Users'],
            datasets: [{
                data: [{{ $casesCount }}, {{ $complaintsCount }}, {{ $usersCount }}],
                backgroundColor: ['#1e90ff', '#f6c23e', '#1cc88a'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection
