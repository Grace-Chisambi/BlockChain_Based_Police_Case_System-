@extends('layouts.prosecutor')

@section('content')
<div class="row">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <!-- Metrics Cards -->
            @foreach ([
                ['icon' => 'gavel', 'title' => 'Total Prosecutions', 'count' => $prosecutionsCount, 'color' => 'success', 'change' => '+4% vs last month'],
                ['icon' => 'file-alt', 'title' => 'Reviewed Cases', 'count' => $reviewedCasesCount, 'color' => 'primary', 'change' => '+10% reviewed'],
                ['icon' => 'clock', 'title' => 'Pending Hearings', 'count' => $pendingHearingsCount, 'color' => 'danger', 'change' => '-3% delayed hearings']
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
                    <h4 class="card-title text-primary mb-3">Prosecution Trends (Last 6 Months)</h4>
                    <canvas id="prosecutionChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <!-- Logs & Doughnut -->
        <div class="col-md-4">
            <div class="card bg-white shadow rounded-3 p-4">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Recent Activities</h4>
                    <canvas id="activityDoughnut" height="200" class="mb-4"></canvas>
                    <div class="activity-log">
                        @forelse($recentActions as $action)
                            <div class="log-entry bg-white shadow-sm d-flex justify-content-between align-items-center p-3 rounded mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $action->action ?? $action->description }}</h6>
                                    <p class="text-muted mb-0"><small>{{ $action->created_at->diffForHumans() }}</small></p>
                                </div>
                            </div>
                        @empty
                            <div class="log-entry bg-white shadow-sm p-3 rounded">
                                <h6 class="mb-1">No recent activity recorded.</h6>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Line Chart
    const ctxLine = document.getElementById('prosecutionChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [
                {
                    label: 'Prosecutions',
                    data: @json($monthlyProsecutions),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pending Cases',
                    data: @json($monthlyPending),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Doughnut Chart
    const ctxDoughnut = document.getElementById('activityDoughnut').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Prosecutions', 'Reviewed', 'Hearings'],
            datasets: [{
                data: [{{ $prosecutionsCount }}, {{ $reviewedCasesCount }}, {{ $pendingHearingsCount }}],
                backgroundColor: ['#28a745', '#007bff', '#dc3545'],
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
