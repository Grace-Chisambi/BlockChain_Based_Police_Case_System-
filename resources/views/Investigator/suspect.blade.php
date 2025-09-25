@extends("layouts.investigator")

@section("content")
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
                    <a href="{{ url('investigator/dash') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('suspect.store') }}">Suspects</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ isset($suspect) ? 'Edit Suspect' : 'Add Suspect' }}</li>
            </ol>
        </nav>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm system-card mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card system-card">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">{{ isset($suspect) ? 'Edit Suspect' : 'Add New Suspect' }}</h4>

            <form id="suspectForm" action="{{ isset($suspect) ? route('suspects.update', $suspect->id) : route('suspect.store') }}" method="POST">
                @csrf
                @if(isset($suspect))
                    @method('PUT')
                @endif

                <div class="mb-4">
                    <label for="case_id" class="form-label fw-semibold text-primary">Assigned Case *</label>
                    <select name="case_id" id="case_id" class="form-select system-input" required>
                        <option value="">-- Select Case --</option>
                        @foreach($assignedCases as $case)
                            <option value="{{ $case->case_id }}" {{ (old('case_id', $suspect->case_id ?? '') == $case->case_id) ? 'selected' : '' }}>
                                {{ $case->case_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="fname" class="form-label fw-semibold text-primary">First Name *</label>
                        <input type="text" class="form-control system-input" name="fname" id="fname" value="{{ old('fname', $suspect->fname ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="sname" class="form-label fw-semibold text-primary">Surname *</label>
                        <input type="text" class="form-control system-input" name="sname" id="sname" value="{{ old('sname', $suspect->sname ?? '') }}" required>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-md-4">
                        <label for="age" class="form-label fw-semibold text-primary">Age *</label>
                        <input type="number" class="form-control system-input" name="age" id="age" value="{{ old('age', $suspect->age ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="village" class="form-label fw-semibold text-primary">Village *</label>
                        <input type="text" class="form-control system-input" name="village" id="village" value="{{ old('village', $suspect->village ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="job" class="form-label fw-semibold text-primary">Job</label>
                        <input type="text" class="form-control system-input" name="job" id="job" value="{{ old('job', $suspect->job ?? '') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <label for="phone_number" class="form-label fw-semibold text-primary">Phone Number</label>
                    <input type="text" class="form-control system-input" name="phone_number" id="phone_number" value="{{ old('phone_number', $suspect->phone_number ?? '') }}">
                </div>

                <div class="mt-4">
                    <label for="statement" class="form-label fw-semibold text-primary">Statement *</label>
                    <textarea class="form-control system-input" name="statement" id="statement" rows="6">{{ old('statement', $suspect->statement ?? '') }}</textarea>
                </div>

                <div class="mt-4">
                    <label for="status" class="form-label fw-semibold text-primary">Status *</label>
                    <select name="status" id="status" class="form-select system-input" required>
                        <option value="">-- Select Status --</option>
                        <option value="detained" {{ old('status', $suspect->status ?? '') === 'detained' ? 'selected' : '' }}>Detained</option>
                        <option value="released" {{ old('status', $suspect->status ?? '') === 'released' ? 'selected' : '' }}>Released</option>
                        <option value="at large" {{ old('status', $suspect->status ?? '') === 'at large' ? 'selected' : '' }}>At Large</option>
                    </select>
                </div>

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5">
                        <i class="fas fa-save me-2"></i> {{ isset($suspect) ? 'Update Suspect' : 'Add Suspect' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CKEditor 5 CDN and Validation -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    let ckEditorInstance;

    ClassicEditor
        .create(document.querySelector('#statement'))
        .then(editor => {
            ckEditorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

    document.getElementById('suspectForm').addEventListener('submit', function (e) {
        const content = ckEditorInstance.getData().trim();
        if (!content) {
            e.preventDefault();
            alert('The statement field is required.');
            ckEditorInstance.editing.view.focus();
        }
    });
</script>
@endsection
