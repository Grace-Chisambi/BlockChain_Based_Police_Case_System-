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

    .system-table th {
        background-color: #f8f9fa;
        color: #0d6efd;
        font-weight: 600;
    }

    .system-table td,
    .system-table th {
        vertical-align: middle;
    }

    .table-responsive {
        margin-top: 1rem;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Suspects Awaiting Review</li>
            </ol>
        </nav>
    </div>

    <!-- Suspects Card -->
    <div class="system-card">
        <h3 class="text-primary fw-bold mb-4">Suspects Awaiting Review</h3>

        @if($suspects->isEmpty())
            <p class="text-muted">No suspects to review at this time.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover system-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Case ID</th>
                            <th>Village</th>
                            <th>Status</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suspects as $suspect)
                            <tr>
                                <td>{{ $suspect->fname }} {{ $suspect->sname }}</td>
                                <td>{{ $suspect->case_id }}</td>
                                <td>{{ $suspect->village }}</td>
                                <td>
                                    <span class="badge bg-{{ $suspect->status === 'Pending' ? 'warning' : 'secondary' }}">
                                        {{ ucfirst($suspect->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.suspect.review', $suspect->suspect_id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-search me-1"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
