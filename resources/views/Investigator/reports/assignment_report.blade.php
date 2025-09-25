@extends('layouts.investigator')

@section('content')
<style>
    .breadcrumb-bar {
        margin-top: -20px;
        margin-left: 2rem;
        margin-bottom: 1.5rem;
    }

    .system-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.05);
        padding: 2.5rem;
        margin-bottom: 2rem;
    }

    table.table {
        border-collapse: separate;
        border-spacing: 0 0.75rem;
    }

    table.table thead tr th {
        background-color: #f8f9fa;
        color: #0d6efd;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }

    table.table tbody tr {
        background-color: #fff;
        box-shadow: 0 0 12px rgba(13, 110, 253, 0.1);
        border-radius: 0.5rem;
        transition: box-shadow 0.3s ease;
    }

    table.table tbody tr:hover {
        box-shadow: 0 0 20px rgba(13, 110, 253, 0.2);
    }

    table.table tbody tr td {
        vertical-align: middle;
        padding: 1rem;
        border: none;
    }

    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        border-radius: 0.4rem;
    }

    .filter-row {
        margin-bottom: 2rem;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Investigator Assignment Report</li>
            </ol>
        </nav>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('investigator.reports.assignment') }}" class="row g-3 filter-row">
        <div class="col-md-3">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From Date">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To Date">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="system-card">
        <h3 class="text-primary fw-bold mb-4 text-center">Investigator Assignment Report</h3>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Investigator / Officer</th>
                        <th>Total Assigned Cases</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignmentStats as $officer)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $officer->sname }}</td>
                            <td>{{ $officer->cases_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No assignments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
