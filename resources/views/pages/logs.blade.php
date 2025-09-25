@extends('layouts.admin')

@section('content')
<style>
    .breadcrumb-bar {
        margin-top: -20px;
        margin-left: 2rem;
        margin-bottom: 1rem;
    }
    .system-card {
        width: 100%;
        max-width: 100%;
        margin: auto;
        border: none;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
        border-radius: 0.75rem;
        background-color: #ffffff;
    }
    .system-card .card-body {
        padding: 3rem;
    }
    .system-table th {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .copy-btn {
        font-size: 0.75rem;
        padding: 2px 6px;
        border: none;
        background-color: #e9ecef;
        color: #333;
        border-radius: 4px;
        cursor: pointer;
    }
    .pagination {
        justify-content: center;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb Bar -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('/') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">System Logs</li>
            </ol>
        </nav>
    </div>

    <!-- System Logs -->
    <div class="card system-card mb-5">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">System Logs & Audit Trails</h4>

            <!-- Filter Form for System Logs -->
            <form method="GET" action="{{ route('logs.index') }}" class="row g-3 mb-4 justify-content-center">
                <div class="col-md-3">
                    <select name="system_date_range" class="form-select">
                        <option value="">-- Date Range --</option>
                        <option value="week" {{ request('system_date_range') == 'week' ? 'selected' : '' }}>Last Week</option>
                        <option value="month" {{ request('system_date_range') == 'month' ? 'selected' : '' }}>Last Month</option>
                        <option value="year" {{ request('system_date_range') == 'year' ? 'selected' : '' }}>Last Year</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle system-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            <tr>
                                <td>{{ $logs->firstItem() + $index }}</td>
                                <td>{{ $log->user->sname ?? 'System' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->module }}</td>
                                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>



           


@endsection
