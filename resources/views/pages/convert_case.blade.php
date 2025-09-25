@extends('layouts.investigator')

@section('content')
<style>
    .breadcrumb-bar {
        margin-top: -20px;
        margin-left: 2rem;
        margin-bottom: 1rem;
    }
    .system-card {
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
    .system-input {
        background-color: #ffffff !important;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    .system-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
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
                <li class="breadcrumb-item active" aria-current="page">Convert Complaint</li>
            </ol>
        </nav>
    </div>

    <!-- Form Card -->
    <div class="card system-card">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">Convert Complaint to Case</h4>

            <div id="alertContainer"></div>

            <form id="convertForm">
                @csrf

                <div class="row g-4">
                    <!-- Complaint Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">Complaint Name *</label>
                        <input type="text" class="form-control system-input" value="{{ $complaint->fname }}" readonly>
                    </div>

                    <!-- Case Type -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">Case Type *</label>
                        <select class="form-select system-input" name="case_type" required>
                            <option value="">Select Case Type</option>
                            <option value="Theft">Theft</option>
                            <option value="Assault">Assault</option>
                            <option value="Fraud">Fraud</option>
                            <option value="Murder">Murder</option>
                            <option value="Cyber Crime">Cyber Crime</option>
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">Allocate to Department *</label>
                        <select class="form-select system-input" name="department_id" required>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::all() as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Case Status -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">Case Status *</label>
                        <select class="form-select system-input" name="case_status" required>
                            <option value="Open">Open</option>
                            <option value="Pending">Pending</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>

                    <!-- Complaint Statement (full width) -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary">Statement *</label>
                        <textarea class="form-control system-input" rows="4" readonly>{{ $complaint->statement }}</textarea>
                    </div>
                </div>

                <input type="hidden" name="complaint_id" value="{{ $complaint->complaint_id }}">

                <!-- Submit Button -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5" id="convertBtn">
                        <i class="fa fa-paper-plane me-2"></i> Convert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('convertForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn = document.getElementById('convertBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Converting...';

    const formData = new FormData(this);

    function showAlert(type, msg) {
        document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    }

    try {
        const response = await fetch("{{ route('cases.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', `Case stored and blockchain hash logged.<br><strong>Hash:</strong> ${data.transaction_hash}`);
            setTimeout(() => location.reload(), 2500);
        } else {
            showAlert('danger', data.message || 'Failed to create case.');
        }
    } catch (err) {
        console.error(err);
        showAlert('danger', 'An unexpected error occurred.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Convert';
    }
});
</script>
@endsection
