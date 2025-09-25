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

    .badge-decision {
        font-size: 0.9rem;
        padding: 0.4em 0.75em;
        border-radius: 0.5rem;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('/supervisor/suspects/pending') }}">Suspects Awaiting Review</a></li>
                <li class="breadcrumb-item active" aria-current="page">Review Suspect</li>
            </ol>
        </nav>
    </div>

    <!-- Review Header -->
    <div class="system-card">
        <h3 class="text-primary fw-bold mb-4">
            Suspect Review – {{ $suspect->fname }} {{ $suspect->sname }}
        </h3>

        @if($suspect->decision && $suspect->recommendation)
            <!-- Already Reviewed Section -->
            <div class="alert alert-info">
                <h5 class="fw-bold text-success mb-3">This suspect has already been reviewed.</h5>
                <p><strong>Decision:</strong>
                    <span class="badge bg-{{ $suspect->decision === 'detain' ? 'danger' : 'success' }} badge-decision text-uppercase">
                        {{ $suspect->decision }}
                    </span>
                </p>
                <p><strong>Recommendation:</strong> {{ $suspect->recommendation }}</p>
            </div>
        @else
            <!-- Review Form -->
            <form method="POST" action="{{ route('supervisor.suspect.review.submit', $suspect->suspect_id) }}">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="recommendation" class="form-label fw-semibold text-primary">Recommendation</label>
                    <textarea class="form-control" name="recommendation" rows="4" required>{{ old('recommendation', $suspect->recommendation) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="decision" class="form-label fw-semibold text-primary">Decision</label>
                    <select name="decision" class="form-select" required>
                        <option value="">-- Select Decision --</option>
                        <option value="detain" {{ $suspect->decision === 'detain' ? 'selected' : '' }}>Detain</option>
                        <option value="release" {{ $suspect->decision === 'release' ? 'selected' : '' }}>Release</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 fs-5">
                        <i class="fas fa-check-circle me-2"></i> Submit Review
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
