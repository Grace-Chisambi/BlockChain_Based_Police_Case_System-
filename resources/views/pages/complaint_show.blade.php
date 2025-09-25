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
                    <a href="{{ route('complaints.review') }}">Complaints</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Complaint Details</li>
            </ol>
        </nav>
    </div>

    <!-- Complaint Details Card -->
    <div class="card system-card">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">Complaint Details</h4>

            <div class="mb-3">
                <p class="mb-2"><strong class="text-primary">Name:</strong> {{ $complaint->name }}</p>
                <p class="mb-2"><strong class="text-primary">Age:</strong> {{ $complaint->age }}</p>
                <p class="mb-2"><strong class="text-primary">Village:</strong> {{ $complaint->village }}</p>
                <p class="mb-2"><strong class="text-primary">Job:</strong> {{ $complaint->job }}</p>
                <p class="mb-2"><strong class="text-primary">Phone Number:</strong> {{ $complaint->phone_number }}</p>
                <p class="mb-2"><strong class="text-primary">Statement:</strong></p>
                <div class="mb-2">{!! $complaint->statement !!}</div>
                <p class="mb-0"><strong class="text-primary">Reported On:</strong> {{ $complaint->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
