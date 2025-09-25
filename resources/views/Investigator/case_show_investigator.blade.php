@extends('layouts.investigator')

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

    .review-chart-title {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .chart-wrapper {
        max-width: 230px;
        margin: auto;
        position: relative;
    }

    .chart-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        font-weight: bold;
        color: #0d6efd;
    }
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ url('investigator/assign') }}">Assigned Cases</a></li>
                <li class="breadcrumb-item active" aria-current="page">Upload Evidence</li>
            </ol>
        </nav>
    </div>

    <!-- Case & Complaint Info -->
    <div class="system-card">
        @include('partials.case_info')
        @include('partials.complaint_info')
    </div>

    <div class="row">
        <!-- LEFT: Upload Form -->
        <div class="col-md-8">
            <div id="alertContainer" class="mb-4"></div>

            <div class="system-card">
                <h4 class="mb-4 text-primary text-center fw-bold">Upload Report / Findings</h4>

                <form id="evidenceForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="case_id" value="{{ $case->case_id }}">

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary">Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <!-- File -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary">Evidence File (optional)</label>
                        <input type="file" name="file" class="form-control">
                    </div>

                    <!-- Submit -->
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5" id="submitEvidenceBtn">
                            <i class="fas fa-upload me-2"></i> Upload Evidence
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: Review Summary Chart -->
        <div class="col-md-4">
            <div class="system-card text-center">
                <div class="review-chart-title text-primary">Supervisor Review Summary</div>
                <div class="chart-wrapper">
                    <canvas id="reviewSummaryChart"></canvas>
                    <div id="chartPercentage" class="chart-percentage">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart Logic -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const approved = {{ $reviewSummary['Approved'] ?? 0 }};
    const total = {{ 
        ($reviewSummary['Approved'] ?? 0) + 
        ($reviewSummary['Rejected'] ?? 0) + 
        ($reviewSummary['Pending'] ?? 0) 
    }};
    const approvedPercent = total > 0 ? ((approved / total) * 100).toFixed(0) : 0;

    const ctx = document.getElementById('reviewSummaryChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Other'],
            datasets: [{
                data: [
                    {{ $reviewSummary['Approved'] ?? 0 }},
                    {{ ($reviewSummary['Approved'] ?? 0) > 0 ? 1 : 1 }}
                ],
                backgroundColor: ['#198754', '#dee2e6'],
                borderWidth: 1
            }]
        },
        options: {
            cutout: '75%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

    document.getElementById('chartPercentage').textContent = approvedPercent + '%';
});
</script>

<!-- Upload Logic -->
<script>
document.getElementById('evidenceForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('submitEvidenceBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

    const formData = new FormData(this);

    const showAlert = (type, msg) => {
        document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    };

    try {
        const response = await fetch("{{ route('investigator.evidence.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const messages = data?.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Failed to save evidence.');
            showAlert('danger', messages);
        } else {
            showAlert('success', `Evidence saved successfully`);

            //  Clear the form to prevent accidental re-submission
            document.getElementById('evidenceForm').reset();
            setTimeout(() => location.reload(), 1500);
            //  refresh part of the UI here (chart/list) if needed
        }
    } catch (err) {
        console.error(err);
        showAlert('danger', 'An unexpected error occurred.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload me-2"></i> Upload Evidence';
    }
});
</script>
@endsection
