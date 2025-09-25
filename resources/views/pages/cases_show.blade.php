@extends('layouts.investigator')

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

    .btn-rounded-pill {
        border-radius: 50rem !important;
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
                <li class="breadcrumb-item">
                    <a href="{{ route('cases.index') }}">Cases</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Case Details</li>
            </ol>
        </nav>
    </div>

    <!-- Case Details Card -->
    <div class="card system-card">
        <div class="card-body">
            <h3 class="mb-4 text-primary text-center fw-bold">Case Details: {{ $case->case_number }}</h3>

            {{-- Include partial views for structured content --}}
            @include('partials.case_info')
            @include('partials.complaint_info')
            @include('partials.evidence_list')
        </div>
    </div>
</div>
@endsection
