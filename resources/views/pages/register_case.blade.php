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
                <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('complaints.review') }}">Review Complaints</a></li>
                <li class="breadcrumb-item active" aria-current="page">Register Complaint</li>
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

    <!-- Complaint Form Card -->
    <div class="card system-card">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">Register a Complaint</h4>

            <form method="POST" action="{{ route('complaint.store') }}" id="complaintForm">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="fname" class="form-label fw-semibold text-primary">First Name *</label>
                        <input type="text" class="form-control system-input" name="fname" id="fname" value="{{ old('fname') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="sname" class="form-label fw-semibold text-primary">Surname *</label>
                        <input type="text" class="form-control system-input" name="sname" id="sname" value="{{ old('sname') }}" required>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-md-4">
                        <label for="age" class="form-label fw-semibold text-primary">Age *</label>
                        <input type="number" class="form-control system-input" name="age" id="age" value="{{ old('age') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label for="village" class="form-label fw-semibold text-primary">Village *</label>
                        <input type="text" class="form-control system-input" name="village" id="village" value="{{ old('village') }}" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="job" class="form-label fw-semibold text-primary">Job *</label>
                    <input type="text" class="form-control system-input" name="job" id="job" value="{{ old('job') }}" required>
                </div>

                <div class="mt-4">
                    <label for="phone_number" class="form-label fw-semibold text-primary">Phone Number</label>
                    <input type="tel" class="form-control system-input" name="phone_number" id="phone_number" value="{{ old('phone_number') }}">
                </div>

                <div class="mt-4">
                    <label for="statement" class="form-label fw-semibold text-primary">Complaint Statement *</label>
                    <textarea name="statement" id="statement" rows="6" class="form-control system-input">{{ old('statement') }}</textarea>
                 </div>

                <!-- Hidden Lat/Long -->
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5">
                        <i class="fas fa-paper-plane me-2"></i> Submit Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Geolocation Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
            }, function (error) {
                console.warn("Geolocation error:", error.message);
            });
        }
    });
</script>

<!-- CKEditor Initialization -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    let editorInstance;
    ClassicEditor.create(document.querySelector('#statement'))
        .then(editor => {
            editorInstance = editor;
        })
        .catch(error => console.error(error));

    document.getElementById('complaintForm').addEventListener('submit', function(e) {
        const content = editorInstance.getData().trim();

        if (!content) {
            alert('Please fill in the complaint statement.');
            e.preventDefault();
            return false;
        }

        // Put the CKEditor content back into textarea for submission
        document.getElementById('statement').value = content;
    });
</script>

@endsection
