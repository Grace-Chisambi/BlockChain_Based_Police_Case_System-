@extends('layouts.apps')

@section('content')
<style>
    .report-summary-box {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        background-color: #ffffff;
        transition: box-shadow 0.2s;
    }
    .report-summary-box:hover {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    .text-decoration-none { text-decoration: none !important; }

    /* Modal custom colors and size */
    #caseStatsModal .modal-content {
        background-color: #e0f7ff; /* light skyblue */
        color: #0a3d62; /* dark blue text */
    }
    #chartMessage span.text-success {
        color: #0c7cd5; /* bright skyblue */
        font-weight: 600;
    }
    #chartMessage span.text-danger {
        color: #ff6b6b;
        font-weight: 600;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Case Report</li>
            </ol>
        </nav>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('cases.report') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From Date">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To Date">
        </div>
        <div class="col-md-3">
            <input type="text" name="assigned_to" value="{{ request('assigned_to') }}" class="form-control" placeholder="Supervisor or Investigator">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2 text-end">
            <a href="{{ route('cases.report.export', ['from' => request('from'), 'to' => request('to'), 'status' => request('status')]) }}" class="btn btn-outline-danger w-100">Export PDF</a>
        </div>
    </form>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('cases.report', request()->except('status')) }}" class="text-decoration-none">
                <div class="report-summary-box">
                    <h5>Total Cases</h5>
                    <h3 class="text-primary">{{ $stats['total'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('cases.report', array_merge(request()->all(), ['status' => 'Open'])) }}" class="text-decoration-none">
                <div class="report-summary-box">
                    <h5>Open</h5>
                    <h3 class="text-success">{{ $stats['open'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('cases.report', array_merge(request()->all(), ['status' => 'Closed'])) }}" class="text-decoration-none">
                <div class="report-summary-box">
                    <h5>Closed</h5>
                    <h3 class="text-secondary">{{ $stats['closed'] }}</h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="report-summary-box" data-bs-toggle="modal" data-bs-target="#caseStatsModal" style="cursor: pointer;">
                <h5>Statistics</h5>
                <h3 class="text-info"><i class="fas fa-chart-bar"></i></h3>
            </div>
        </div>
    </div>

    <!-- Case Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3 text-primary">
                @if(request('status')) {{ request('status') }} @endif Cases Report
            </h5>

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $case)
                        <tr>
                            <td class="fw-bold">{{ $case->case_number }}</td>
                            <td>{{ $case->case_type }}</td>
                            <td>
                                @php
                                    $status = strtolower($case->case_status);
                                    $statusColor = $status === 'open' ? 'success' : ($status === 'pending' ? 'warning' : 'secondary');
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $case->case_status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst($case->priority) }}</span>
                            </td>
                            <td>{{ $case->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No cases found for selected criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {{ $cases->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal with Line Chart -->
<div class="modal fade" id="caseStatsModal" tabindex="-1" aria-labelledby="caseStatsLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered"><!-- changed to modal-md -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Case Trend: Open vs Closed ({{ request('from') ?? 'All' }} to {{ request('to') ?? 'Now' }})</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <canvas id="lineChartCanvas" height="120"></canvas>

        <!-- Summary of total, open, closed cases inside modal -->
        <div class="mt-4 d-flex justify-content-around text-center">
          <div>
            <h6>Total Cases</h6>
            <p class="h4 text-primary">{{ $stats['total'] }}</p>
          </div>
          <div>
            <h6>Open Cases</h6>
            <p class="h4 text-success">{{ $stats['open'] }}</p>
          </div>
          <div>
            <h6>Closed Cases</h6>
            <p class="h4 text-secondary">{{ $stats['closed'] }}</p>
          </div>
        </div>

        <div class="mt-3 text-center fw-bold" id="chartMessage"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let lineChart;

    const labels = @json($lineChartData['labels']);
    const openData = @json($lineChartData['open']);
    const closedData = @json($lineChartData['closed']);

    const chartConfig = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Open Cases',
                    data: openData,
                    borderColor: '#87CEEB', // skyblue border
                    backgroundColor: 'rgba(135, 206, 235, 0.3)', // skyblue fill
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#87CEEB',
                    pointBorderColor: '#87CEEB',
                },
                {
                    label: 'Closed Cases',
                    data: closedData,
                    borderColor: '#1E90FF', // dodgerblue border
                    backgroundColor: 'rgba(30, 144, 255, 0.3)', // dodgerblue fill
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1E90FF',
                    pointBorderColor: '#1E90FF',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    };

    document.getElementById('caseStatsModal').addEventListener('shown.bs.modal', function () {
        const ctx = document.getElementById('lineChartCanvas').getContext('2d');
        if (lineChart) lineChart.destroy();
        lineChart = new Chart(ctx, chartConfig);

        const totalOpen = openData.reduce((a, b) => a + b, 0);
        const totalClosed = closedData.reduce((a, b) => a + b, 0);

        const msg = document.getElementById('chartMessage');
        msg.innerHTML = totalClosed >= totalOpen
            ? '<span class="text-success">More cases are getting closed than opened.</span>'
            : '<span class="text-danger">More cases are opened than closed during this period.</span>';

    });
document.getElementById('caseStatsModal').addEventListener('hidden.bs.modal', function () {
    window.location.href = "{{ route('cases.report') }}";
});


});
</script>

@endsection
