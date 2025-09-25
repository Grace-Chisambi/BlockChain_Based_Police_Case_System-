@extends('layouts.investigator')

@section('content')
<div class="row">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <!-- Metrics Cards -->
            @foreach ([
                ['icon' => 'folder-open', 'title' => 'Total Cases', 'count' => $casesCount, 'color' => 'success', 'change' => '+5% from last month'],
                ['icon' => 'exclamation-circle', 'title' => 'Total Complaints', 'count' => $complaintsCount, 'color' => 'warning', 'change' => '+8% new complaints'],
                ['icon' => 'users', 'title' => 'Police Staff', 'count' => $usersCount, 'color' => 'info', 'change' => '+2% staff growth']
            ] as $item)
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-white shadow-sm rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-{{ $item['icon'] }} fa-3x text-primary"></i>
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

    <!-- Graph Section -->
    <div class="row mt-4">
        <!-- Line Chart -->
        <div class="col-md-8">
            <div class="card bg-white shadow rounded-3 p-4">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Case Statistics (Last 6 Months)</h4>
                    <canvas id="caseChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <!-- Logs & Doughnut -->
        <div class="col-md-4">
            <div class="card bg-white shadow rounded-3 p-4">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Recent Activity</h4>
                    <canvas id="transaction-history" height="200" class="mb-4"></canvas>
                    <div class="transaction-history">
                        @forelse($latestLogs as $log)
                            <div class="log-entry bg-white shadow-sm d-flex justify-content-between align-items-center p-3 rounded mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $log->action }}</h6>

                                    <p class="text-muted mb-0"><small>{{ $log->created_at->diffForHumans() }}</small></p>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxLine = document.getElementById('caseChart').getContext('2d');
    const caseChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [
                {
                    label: 'Open Cases',
                    data: @json($openCases),
                    borderColor: '#1e90ff',
                    backgroundColor: 'rgba(30, 144, 255, 0.2)',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pending Cases',
                    data: @json($pendingCases),
                    borderColor: '#87cefa',
                    backgroundColor: 'rgba(135, 206, 250, 0.2)',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Closed Cases',
                    data: @json($closedCases),
                    borderColor: '#4682b4',
                    backgroundColor: 'rgba(70, 130, 180, 0.2)',
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const ctxDoughnut = document.getElementById('transaction-history').getContext('2d');
    const transactionChart = new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Cases', 'Complaints', 'Users'],
            datasets: [{
                data: [{{ $casesCount }}, {{ $complaintsCount }}, {{ $usersCount }}],
                backgroundColor: ['#4e73df', '#f6c23e', '#1cc88a'],
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
