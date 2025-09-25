@extends('layouts.apps')

@section('content')
<div class="container py-4">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">Allocate Case to Department</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('cases.allocate.store') }}" method="POST">
                @csrf
                <input type="hidden" name="complaint_id" value="{{ $complaint->complaint_id }}">

                <div class="mb-3">
                    <label class="form-label">Complainant Name</label>
                    <input type="text" class="form-control" value="{{ $complaint->name }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Statement</label>
                    <textarea class="form-control" rows="4" readonly>{{ $complaint->statement }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Select Department</label>
                    <select class="form-control" id="department_id" name="department_id" required>
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Allocate</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
