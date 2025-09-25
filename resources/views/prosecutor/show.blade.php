@extends('layouts.prosecutor')

@section('content')
<style>
    .breadcrumb-bar {
        margin-top: 10px;
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

    .evidence-card {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 0 12px rgba(13, 110, 253, 0.1);
        background-color: #fff;
        transition: box-shadow 0.3s ease;
    }

    .evidence-card:hover {
        box-shadow: 0 0 20px rgba(13, 110, 253, 0.2);
    }

    label {
        font-weight: 600;
        color: #0d6efd;
    }

    h2, h4 {
        color: #0d6efd;
        margin-bottom: 1rem;
    }
</style>

<div class="container py-4">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('prosecutor/dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('prosecutor/cases') }}">Assigned Cases</a></li>
                <li class="breadcrumb-item active" aria-current="page">Case Details</li>
            </ol>
        </nav>
    </div>

    <!-- Case & Complaint Info -->
    <div class="system-card">
        @include('partials.case_info')
        @include('partials.complaint_info')
    </div>

    <!-- Evidence Section -->
    <div class="system-card">
        <h4 class="mb-4 text-primary fw-bold text-center">Evidence</h4>

        @if($case->evidence && $case->evidence->count() > 0)
            @foreach($case->evidence as $evidence)
                <div class="evidence-card">
                    <p><strong>Description:</strong> {{ $evidence->description }}</p>
                    <p><strong>Status:</strong> {{ $evidence->review_status ?? 'Pending' }}</p>

                    @if($evidence->file_path)
                        <p><strong>File:</strong>
                            <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View / Download
                            </a>
                        </p>
                    @else
                        <p class="text-muted">No file uploaded for this evidence.</p>
                    @endif
                </div>
            @endforeach
        @else
            <p class="text-muted text-center">No evidence found for this case.</p>
        @endif
    </div>
</div>
@endsection
