@extends('layouts.prosecutor')

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

    .no-data {
        background-color: #e3f2fd;
        color: #0d6efd;
        border: 1px solid #b6e0fe;
    }

    .bulk-actions {
        margin-bottom: 1rem;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('prosecutor/dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Court Appearances</li>
            </ol>
        </nav>
    </div>

    <div class="system-card">
        <h3 class="text-primary fw-bold mb-4 text-center">Upcoming Court Appearances</h3>

        @if($appearances->isEmpty())
            <div class="alert alert-info text-center no-data">No upcoming court appearances.</div>
        @else
            <!-- Bulk Action Form -->
            <form method="POST" action="{{ route('prosecutor.exportSelectedPdf') }}">
                @csrf
                <div class="bulk-actions d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" id="select-all">
                        <label for="select-all" class="ms-1">Select All</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success">Export Selected to PDF</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>Case Number</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Court</th>
                                <th>Location</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appearances as $index => $appearance)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_cases[]" value="{{ $appearance->case_id }}" class="case-checkbox">
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $appearance->case_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appearance->date)->format('F j, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appearance->time)->format('g:i A') }}</td>
                                    <td>{{ $appearance->court_name ?? '—' }}</td>
                                    <td>{{ $appearance->location ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('prosecutor.cases.show', $appearance->case_id) }}" class="btn btn-sm btn-primary">
                                            View Case
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
    document.getElementById('select-all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.case-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
