@extends('layouts.admin')

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
                <li class="breadcrumb-item">
                    <a href="{{ url('/') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('users.index') }}">Users</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Add User</li>
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

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="fname" class="form-label fw-semibold text-primary">First Name *</label>
                        <input type="text" name="fname" id="fname" value="{{ old('fname') }}" class="form-control system-input" required>
                    </div>
                    <div class="col-md-6">
                        <label for="sname" class="form-label fw-semibold text-primary">Surname *</label>
                        <input type="text" name="sname" id="sname" value="{{ old('sname') }}" class="form-control system-input" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="email" class="form-label fw-semibold text-primary">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control system-input" required>
                </div>

                <div class="mt-4">
                    <label for="role" class="form-label fw-semibold text-primary">Role *</label>
                    <select name="role" id="role" class="form-select system-input" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="investigator" {{ old('role') == 'investigator' ? 'selected' : '' }}>Investigator</option>
                        <option value="police_officer" {{ old('role') == 'police_officer' ? 'selected' : '' }}>Police Officer</option>
                        <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                         <option value="prosecutor" {{ old('role') == 'prosecutor' ? 'selected' : '' }}>Prosecutor</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label for="password" class="form-label fw-semibold text-primary">Password *</label>
                    <input type="password" name="password" id="password" class="form-control system-input" required>
                </div>

                <div class="mt-4">
                    <label for="password_confirmation" class="form-label fw-semibold text-primary">Confirm Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control system-input" required>
                </div>

                <!-- Start Police Staff Fields -->
                <hr class="my-5">
                <h5 class="text-primary fw-bold mb-4">Police Staff Details</h5>

                <div class="mt-4">
                    <label for="department_id" class="form-label fw-semibold text-primary">Department *</label>
                    <select name="department_id" id="department_id" class="form-select system-input" required>
                        <option value="" disabled selected>Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_id }}" {{ old('department_id') == $department->department_id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="specialization" class="form-label fw-semibold text-primary">Specialization</label>
                    <input type="text" name="specialization" id="specialization" class="form-control system-input" value="{{ old('specialization') }}">
                </div>

                <div class="mt-4">
                    <label for="available" class="form-label fw-semibold text-primary">Available</label>
                    <select name="available" id="available" class="form-select system-input">
                        <option value="1" {{ old('available') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('available') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <!-- End Police Staff Fields -->

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5">
                        <i class="fas fa-user-plus me-2"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
