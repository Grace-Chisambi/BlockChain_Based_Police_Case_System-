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

    .filter-form {
        margin-bottom: 1.5rem;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Assigned Cases</li>
            </ol>
        </nav>
    </div>

    <div class="system-card">
        <h3 class="text-primary fw-bold mb-4 text-center">Assigned Cases</h3>

        <!-- Filter & Report -->
        <form method="GET" action="{{ route('investigator.assigned_cases') }}" class="row g-3 filter-form">
            <div class="col-md-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To Date">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Statuses</option>
                    <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-50">Search</button>
            {{--  <a href="{{ route('investigator.assignment_report', request()->only(['from', 'to', 'status'])) }}"
                   class="btn btn-outline-success w-50">
                    Report
                </a>  --}}
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Case Number</th>
                        <th>Status</th>
                        <th>Case Type</th>
                        <th>Assigned On</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedCases as $case)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $case->case_number }}</td>
                            <td>{{ $case->case_status }}</td>
                            <td>{{ $case->case_type }}</td>
                            <td>{{ $case->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('investigator.cases.show', $case->case_id) }}" class="btn btn-sm btn-primary">
                                    View
                                </a>
                                <a href="{{ route('investigator.cases.report', $case->case_id) }}" class="btn btn-sm btn-outline-success">
                                    Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No cases assigned.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
