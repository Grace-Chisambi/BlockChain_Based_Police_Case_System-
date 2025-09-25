@extends('layouts.apps')

@section('content')
<style>
    :root {
        --sky-blue: #00bfff;
        --light-sky: #e6f7ff;
    }

    .breadcrumb-bar {
        margin-top: -20px;
        margin-left: 2rem;
        margin-bottom: 1rem;
    }

    .system-card {
        width: 100%;
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
        background-color: var(--light-sky);
        color: var(--sky-blue);
        font-weight: 600;
    }

    .progress-bar.bg-primary {
        background-color: var(--sky-blue) !important;
    }

    .btn-outline-primary {
        border-color: var(--sky-blue);
        color: var(--sky-blue);
    }

    .btn-outline-primary:hover {
        background-color: var(--sky-blue);
        color: white;
    }

    .text-primary {
        color: var(--sky-blue) !important;
    }

    .urgent {
        background-color: #fff0f0 !important;
    }

    .pagination {
        justify-content: center;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Pending Evidence Cases</li>
            </ol>
        </nav>
    </div>

    <div class="card system-card mb-5">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">Pending Evidence Cases</h4>

            <!-- 🔍 Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search case number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- 📊 Stats Summary -->
            <div class="alert alert-light text-center">
                <strong>Total Cases:</strong> {{ $cases->total() }} &nbsp; | &nbsp;
                <strong>Urgent:</strong> {{ $cases->where('priority', 'High')->count() }} &nbsp; | &nbsp;
                <strong>Overdue:</strong> {{ $cases->filter(fn($c) => $c->created_at < now()->subDays(7))->count() }}
            </div>

            @if($cases->isEmpty())
                <div class="alert alert-info text-center">
                    No cases pending your review or assignment.
                </div>
            @else
                <form method="POST" action="{{ route('cases.bulk.action') }}">
                    @csrf

                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <select name="bulk_action" class="form-select form-select-sm" style="width: 200px;" required>
                                <option value="">Bulk Actions</option>
                                <option value="mark_reviewed">Mark All Reviewed</option>
                                <option value="flag_urgent">Mark as Urgent</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger">Apply</button>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle system-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" onclick="toggleAll(this)"></th>
                                    <th>Case Number</th>
                                    <th>Type</th>
                                    <th>Review Summary</th>
                                    <th>Priority</th>
                                    <th>Timeline</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cases as $case)
                                    @php
                                        $total = $case->evidence->count();
                                        $reviewed = $case->evidence->whereIn('review_status', ['Approved', 'Rejected'])->count();
                                        $isUrgent = strtolower($case->priority) === 'high';
                                        $isOverdue = $case->created_at < now()->subDays(7);
                                    @endphp
                                    <tr class="{{ $isUrgent || $isOverdue ? 'urgent' : '' }}">
                                        <td><input type="checkbox" name="case_ids[]" value="{{ $case->case_id }}"></td>
                                        <td><strong>{{ $case->case_number }}</strong></td>
                                        <td>{{ $case->case_type }}</td>
                                        <td>
                                            <span class="text-success fw-bold">{{ $reviewed }}</span> of 
                                            <span class="fw-bold">{{ $total }}</span> reviewed
                                            <div class="progress mt-1" style="height: 5px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $total > 0 ? ($reviewed / $total) * 100 : 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $isUrgent ? 'bg-danger' : 'bg-secondary' }}">
                                                {{ $case->priority }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#timelineModal{{ $case->case_id }}">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <a href="{{ route('review.evidence.page', $case->case_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Timeline Modal -->
                                    <div class="modal fade" id="timelineModal{{ $case->case_id }}" tabindex="-1" aria-labelledby="timelineModalLabel{{ $case->case_id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="timelineModalLabel{{ $case->case_id }}">
                                                        Case Timeline - {{ $case->case_number }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="list-group">
                                                        <li class="list-group-item">
                                                            <strong>Created:</strong> {{ $case->created_at->format('d M Y, h:i A') }}
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Evidence Uploaded:</strong> {{ $case->evidence->count() }} file(s)
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Evidence Reviewed:</strong> {{ $reviewed }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $cases->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleAll(source) {
        let checkboxes = document.querySelectorAll('input[name="case_ids[]"]');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }
</script>
@endsection
