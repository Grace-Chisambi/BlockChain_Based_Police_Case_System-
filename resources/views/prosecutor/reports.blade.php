@extends('layouts.prosecutor')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
    .report-summary-box {
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
        background-color: #ffffff;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .report-summary-box:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        transform: translateY(-4px);
        cursor: pointer;
    }

    .btn-export {
        background-color: #0d6efd;
        color: white;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: none;
        width: 180px;
        margin: 2rem auto 0;
        display: block;
        transition: background-color 0.3s ease;
    }

    .btn-export:hover {
        background-color: #084fc1;
    }

    .chart-container {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 0 25px rgba(13, 110, 253, 0.08);
        margin-bottom: 2rem;
    }

    .icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .insight-box {
        background-color: #f8f9fa;
        border-left: 5px solid #0d6efd;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        border-radius: 0.5rem;
        font-size: 1rem;
    }

    .breadcrumb-bar {
        background-color: #f1f3f5;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
    }

    .breadcrumb i {
        margin-right: 4px;
    }

    h5 {
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .report-summary-box {
            padding: 1.25rem;
        }
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('/prosecutor/dashboard') }}"><i class="fas fa-home"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Case Report</li>
            </ol>
        </nav>
    </div>

    <h2 class="mb-4 text-center text-primary">Reports & Analytics</h2>

    @if($totalCases === 0)
        <div class="alert alert-warning text-center">No case data available.</div>
    @else
        @if($openCases > $closedCases)
            <div class="insight-box">
                <strong>Insight:</strong> There are more open cases than closed ones. You might consider prioritizing case closures.
            </div>
        @endif
    @endif

    <!-- Filter -->
    <form method="GET" class="row g-3 mb-5">
        <div class="col-md-4">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-4">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-4 d-grid">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <div class="col">
            <div class="report-summary-box">
                <div class="icon text-primary"><i class="fas fa-folder"></i></div>
                <h5>Total Cases</h5>
                <h3 class="text-primary">{{ $totalCases }}</h3>
            </div>
        </div>
        <div class="col">
            <div class="report-summary-box">
                <div class="icon text-success"><i class="fas fa-hourglass-half"></i></div>
                <h5>Open Cases</h5>
                <h3 class="text-success">{{ $openCases }}</h3>
            </div>
        </div>
        <div class="col">
            <div class="report-summary-box">
                <div class="icon text-secondary"><i class="fas fa-check-circle"></i></div>
                <h5>Closed Cases</h5>
                <h3 class="text-secondary">{{ $closedCases }}</h3>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="row mb-5">
        <div class="col-md-4">
            <a href="{{ route('prosecutor.cases') }}" class="text-decoration-none">
                <div class="report-summary-box">
                    <div class="icon text-info"><i class="fas fa-list"></i></div>
                    <h5>Go to Case List</h5>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="chart-container">
                <h6 class="text-center">Case Status Overview</h6>
                <canvas id="caseStatusChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h6 class="text-center">Monthly Case Trends</h6>
                <canvas id="monthlyCasesChart"></canvas>
            </div>
        </div>
        <div class="col-md-12">
            <div class="chart-container">
                <h6 class="text-center">Case Type Breakdown</h6>
                <canvas id="caseTypeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Export -->
    <form action="{{ route('prosecutor.reports.pdf') }}" method="GET">
        <button type="submit" class="btn-export">Export to PDF</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const statusCtx = document.getElementById('caseStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Open Cases', 'Closed Cases'],
            datasets: [{
                data: [{{ $openCases }}, {{ $closedCases }}],
                backgroundColor: ['#0d6efd', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const monthCtx = document.getElementById('monthlyCasesChart').getContext('2d');
    new Chart(monthCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Cases Filed',
                data: {!! json_encode($monthlyCounts) !!},
                backgroundColor: '#198754'
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });

    const typeCtx = document.getElementById('caseTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($caseTypeBreakdown->keys()) !!},
            datasets: [{
                label: 'Cases',
                data: {!! json_encode($caseTypeBreakdown->values()) !!},
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection
