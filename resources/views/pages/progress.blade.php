@extends('layouts.apps')

@section('content')
<style>
    .breadcrumb-bar {
        margin-left: 2rem;
        margin-bottom: 1.5rem;
    }

    .system-card {
        background-color: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        margin-top: 1rem;
    }

    .btn-sm {
        padding: 0.35rem 0.7rem;
        font-size: 0.85rem;
        border-radius: 0.4rem;
    }

    .case-header {
        background: #f1f5f9;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        border: 1px solid #dee2e6;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .progress-cards-row {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .progress-cards-col {
        flex: 1;
    }

    .progress-card {
        border-radius: 0.5rem;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        background-color: #fff;
        margin-bottom: 1rem;
    }

    .pending-card {
        background-color: #e0f7ff;
        border-left: 4px solid #0ea5e9;
    }

    .pending-card .btn-outline-primary {
        color: #0ea5e9;
        border-color: #0ea5e9;
    }

    .pending-card .btn-outline-primary:hover {
        background-color: #0ea5e9;
        color: #fff;
    }

    .approved-card {
        border-left: 4px solid #0ea5e9;
        background-color: #f8fafc;
    }

    .modal-body p {
        margin-bottom: 0.5rem;
    }

    .filter-form {
        margin-bottom: 1.5rem;
        margin-top: 0.5rem;
    }

    .nav-tabs {
        border-bottom: 2px solid #e0f2fe;
        margin-bottom: 1rem;
    }

    .nav-tabs .nav-link.active {
        background-color: #0ea5e9;
        color: #fff;
        border-color: transparent;
    }

    .nav-tabs .nav-link {
        color: #0ea5e9;
    }

    .accordion-item {
        border: 1px solid #e3f2fd;
    }

    .accordion-button:not(.collapsed) {
        background-color: #f0f9ff;
        color: #0c4a6e;
    }

    .accordion-button {
        padding: 1rem;
    }
</style>

<div class="container py-5">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('assign') }}">Assign</a></li>
                <li class="breadcrumb-item active" aria-current="page">Case Progress</li>
            </ol>
        </nav>
    </div>

    <div class="system-card">
        <h3 class="text-center text-primary fw-bold mb-4">Case Progress</h3>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3">
            @php $status = request('status'); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $status === null ? 'active' : '' }}" href="{{ route('progress.index') }}">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'open' ? 'active' : '' }}" href="{{ route('progress.index', ['status' => 'open']) }}">Open</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'closed' ? 'active' : '' }}" href="{{ route('progress.index', ['status' => 'closed']) }}">Closed</a>
            </li>
        </ul>

        <!-- Filter Form -->
        <div class="filter-form">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-6 d-flex">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Search cases...">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select">
                        <option value="" {{ is_null(request('priority')) ? 'selected' : '' }}>All Priorities</option>
                        <option value="High" {{ request('priority') === 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ request('priority') === 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
            </form>
        </div>

        @if($assignedCases->isEmpty())
            <div class="alert alert-info mt-4">No cases have been assigned yet.</div>
        @else

            <!-- Table Header -->
            <div class="case-header">
                <div class="row">
                    <div class="col-md-4">Case Number / Type</div>
                    <div class="col-md-2">Status</div>
                    <div class="col-md-2">Priority</div>
                    <div class="col-md-4 text-end">Assigned</div>
                </div>
            </div>

            <div class="accordion" id="casesAccordion">
                @foreach($assignedCases as $case)
                    <div class="accordion-item mb-3 shadow-sm rounded">
                        <h2 class="accordion-header" id="heading-{{ $case->case_id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $case->case_id }}">
                                <div class="d-flex justify-content-between w-100">
                                    <div>
                                        <strong>Case #{{ $case->case_number }}</strong> — {{ $case->case_type ?? 'Unknown' }}
                                        <span class="badge bg-{{ strtolower($case->case_status) === 'open' ? 'success' : 'secondary' }} ms-2">{{ $case->case_status }}</span>
                                        <span class="badge bg-info ms-2">{{ $case->priority ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        Assigned: {{ \Carbon\Carbon::parse($case->assigned_at)->diffForHumans() }}
                                    </div>
                                </div>
                            </button>
                        </h2>

                        <div id="collapse-{{ $case->case_id }}" class="accordion-collapse collapse" data-bs-parent="#casesAccordion">
                            <div class="accordion-body">
                                <div class="text-end mb-3">
                                    <a href="{{ route('investigator.cases.report.pdf', $case->case_id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i> Download Report
                                    </a>
                                </div>

                                <div class="progress-cards-row">
                                    <!-- Pending -->
                                    <div class="progress-cards-col">
                                        @php $pending = $case->progress->where('action', null); @endphp
                                        @forelse($pending as $entry)
                                            <div class="progress-card pending-card">
                                                <div class="fw-bold mb-1">{{ $entry->fname }} {{ $entry->sname }}</div>
                                                <small>{{ \Carbon\Carbon::parse($entry->date)->format('M d, Y') }}</small>
                                                <p class="mt-2">{{ $entry->notes }}</p>
                                                <span class="badge bg-light text-primary mb-2">Pending Approval</span>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-{{ $entry->progress_id }}">
                                                    Approve / Reject
                                                </button>

                                                <!-- Modal -->
                                                <div class="modal fade" id="modal-{{ $entry->progress_id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $entry->progress_id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <form method="POST" action="{{ route('progress.approve') }}">
                                                            @csrf
                                                            <input type="hidden" name="progress_id" value="{{ $entry->progress_id }}">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Action Required</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p><strong>Investigator:</strong> {{ $entry->fname }} {{ $entry->sname }}</p>
                                                                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($entry->date)->format('M d, Y') }}</p>
                                                                    <p><strong>Notes:</strong> {{ $entry->notes }}</p>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Action</label>
                                                                        <select name="action" class="form-select" required>
                                                                            <option value="">Choose</option>
                                                                            <option value="approve">Approve</option>
                                                                            <option value="reject">Reject</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Recommendation (optional)</label>
                                                                        <textarea name="recommendation" class="form-control" rows="3" placeholder="Optional comment..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No pending items.</p>
                                        @endforelse
                                    </div>

                                    <!-- Approved / Rejected -->
                                    <div class="progress-cards-col">
                                        @php $approved = $case->progress->whereIn('action', ['approve', 'reject']); @endphp
                                        @forelse($approved as $entry)
                                            <div class="progress-card approved-card">
                                                <div class="fw-bold mb-1">{{ $entry->fname }} {{ $entry->sname }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($entry->date)->format('M d, Y') }}</small>
                                                <p class="mt-2">{{ $entry->notes }}</p>
                                                <span class="badge {{ $entry->action === 'approve' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($entry->action) }}
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-muted">No approved/rejected items yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $assignedCases->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
