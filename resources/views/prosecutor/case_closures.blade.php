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

    .form-control, .form-select {
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('prosecutor/dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('prosecutor/cases') }}">Assigned Cases</a></li>
                <li class="breadcrumb-item active" aria-current="page">Close Case</li>
            </ol>
        </nav>
    </div>

    <!-- Case Closure Card -->
    <div class="system-card">
        <h4 class="mb-4 text-primary text-center fw-bold">Close Case</h4>

        <div id="alertContainer" class="mb-4"></div>

        @if($cases->isEmpty())
            <div class="alert alert-warning">
                No active cases are currently assigned to you for closure.
            </div>
        @else
            <form id="closureForm">
                @csrf

                <!-- Select Case -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">Select Case to Close</label>
                    <select class="form-select" name="case_id" required>
                        <option value="">Select Case</option>
                        @foreach($cases as $case)
                            <option value="{{ $case->case_id }}">
                                {{ $case->case_number }} - {{ $case->case_type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Closure Type -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">Closure Type</label>
                    <select class="form-select" name="closure_type" required>
                        <option value="">Select Closure Type</option>
                        <option value="permanent">Permanent</option>
                        <option value="temporary">Temporary</option>
                        <option value="withdrawn">Withdrawn</option>
                    </select>
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">Reason</label>
                    <textarea class="form-control" name="reason" rows="3" required></textarea>
                </div>

                <!-- Closure Date -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">Closure Date</label>
                    <input type="date" class="form-control" name="closure_date" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5" id="submitClosureBtn">
                        <i class="fas fa-folder-minus me-2"></i> Submit Closure
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
document.getElementById('closureForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('submitClosureBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

    const formData = new FormData(this);

    function showAlert(type, msg) {
        document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    }

    try {
        const response = await fetch("{{ route('prosecutor.case_closures.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const messages = data?.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Failed to submit closure.');
            showAlert('danger', messages);
        } else {
            showAlert('success', `Case closed successfully.`);
            setTimeout(() => location.reload(), 2500);
        }
    } catch (err) {
        console.error(err);
        showAlert('danger', 'An unexpected error occurred.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-folder-minus me-2"></i> Submit Closure';
    }
});
</script>
@endsection
