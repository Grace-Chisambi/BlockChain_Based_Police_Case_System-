@extends('layouts.apps')

@section('content')
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('cases.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to All Cases
        </a>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0  text-white">Edit Case: {{ $case->case_number }}</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('cases.update', $case->case_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="case_number" class="form-label">Case Number</label>
                        <input type="text" name="case_number" id="case_number" class="form-control" value="{{ old('case_number', $case->case_number) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="case_status" class="form-label">Status</label>
                        <select name="case_status" id="case_status" class="form-select" required>
                            <option value="Open" {{ $case->case_status == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="Pending" {{ $case->case_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Closed" {{ $case->case_status == 'Closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="case_description" class="form-label">Description</label>
                    <textarea name="case_description" id="case_description" rows="4" class="form-control" required>{{ old('case_description', $case->case_description) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Case
                </button>
                <a href="{{ route('cases.show', $case->case_id) }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
