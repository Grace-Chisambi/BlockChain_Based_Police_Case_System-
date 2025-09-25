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
        padding: 2rem;
    }
    .system-table th {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb Bar -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('/investigator/dash') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Suspect Review</li>
            </ol>
        </nav>
    </div>

    <!-- Suspect Review Card -->
    <div class="card system-card">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <a href="{{ url('/investigator/suspect') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Add Suspect
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle system-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Statement</th>
                            <th>Status</th>
                            <th>Case Number</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suspects as $suspect)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $suspect->sname }}</td>
                            <td>{{ Str::limit(strip_tags($suspect->statement), 50, '...') }}</td>
                            <td>{{ ucfirst($suspect->status) }}</td>
                            <td>{{ $suspect->case->case_number ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('suspects.show', $suspect->suspect_id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No suspects found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $suspects->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
