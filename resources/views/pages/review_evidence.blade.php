@extends('layouts.apps')

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

    .form-control, .form-select {
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .evidence-box {
        background: #f9f9f9;
        border-left: 5px solid #0d6efd;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .evidence-box.approved {
        border-left-color: #198754;
    }

    .evidence-box.rejected {
        border-left-color: #dc3545;
    }

    .table-header {
        font-weight: bold;
        color: #0d6efd;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .badge-status {
        font-size: 0.85rem;
        padding: 0.4em 0.6em;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('/pending_cases') }}">Cases</a></li>
                <li class="breadcrumb-item active" aria-current="page">Evidence Review</li>
            </ol>
        </nav>
    </div>

    <!-- Case Info Card -->
    <div class="system-card">
        <h3 class="text-primary fw-bold mb-3">Evidence Review – {{ $case->case_number }}</h3>

        @if(session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif

        <a href="{{ route('evidence.export', $case->case_id) }}" class="btn btn-outline-primary mb-3">
            <i class="fas fa-download me-2"></i> Download Reviewed Evidence (PDF)
        </a>

        @if($case->evidence->where('review_status', 'Pending')->count() === 0)
            <div class="alert alert-success shadow-sm mb-0">
                All evidence has been reviewed.
            </div>
        @endif
    </div>

    <!-- Pending Evidence -->
    <div class="system-card">
        <h4 class="text-primary fw-bold mb-4">Pending Evidence</h4>

        @forelse ($pendingEvidence as $evidence)
            <div class="evidence-box">
                <p><strong>Description:</strong> {{ $evidence->description }}</p>
                <p><strong>Uploaded By:</strong> {{ $evidence->uploader->user->sname ?? 'N/A' }}</p>


                @if($evidence->file_path)
                    <p>
                        <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-alt me-1"></i> View File
                        </a>
                    </p>
                @endif

                <form action="{{ route('evidence.review.submit', $evidence->evidence_id) }}" method="POST" class="mt-3">
                    @csrf
                    <div class="mb-3">
                        <label for="review_status" class="form-label fw-semibold text-primary">Decision:</label>
                        <select name="review_status" class="form-select" required>
                            <option value="Approved">Approve</option>
                            <option value="Rejected">Reject</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="review_comment" class="form-label fw-semibold text-primary">Comment (optional):</label>
                        <textarea name="review_comment" class="form-control" rows="3" placeholder="Enter comments..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Submit Review
                    </button>
                </form>
            </div>
        @empty
            <p class="text-muted">No pending evidence.</p>
        @endforelse

        <div class="d-flex justify-content-center mt-4">
            {{ $pendingEvidence->appends(['reviewed_page' => request()->input('reviewed_page')])->links() }}
        </div>
    </div>

    <!-- Reviewed Evidence -->
    <div class="system-card">
        <h4 class="text-success fw-bold mb-4">Reviewed Evidence</h4>

        @forelse ($reviewedEvidence as $evidence)
            <div class="evidence-box {{ strtolower($evidence->review_status) }}">
                <p><strong>Description:</strong> {{ $evidence->description }}</p>
                <p>
                    <strong>Status:</strong>
                    <span class="badge badge-status bg-{{ $evidence->review_status === 'Approved' ? 'success' : 'danger' }}">
                        {{ $evidence->review_status }}
                    </span>
                </p>
                <p><strong>Comment:</strong> {{ $evidence->review_comment ?? 'N/A' }}</p>
                <p><strong>Reviewed At:</strong> {{ \Carbon\Carbon::parse($evidence->reviewed_at)->format('d M Y H:i') }}</p>
                <p><strong>Reviewed By:</strong> {{ $evidence->reviewer->user->sname ?? 'N/A' }}</p>


                @if($evidence->file_path)
                    <p>
                        <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-alt me-1"></i> View File
                        </a>
                    </p>
                @endif
            </div>
        @empty
            <p class="text-muted">No reviewed evidence yet.</p>
        @endforelse

        <div class="d-flex justify-content-center mt-4">
            {{ $reviewedEvidence->appends(['pending_page' => request()->input('pending_page')])->links() }}
        </div>
    </div>
</div>
@endsection
