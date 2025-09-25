<!-- resources/views/partials/complaint_info.blade.php -->
<div class="card mt-3">
    <div class="card-header">
        Complaint Information
    </div>
    <div class="card-body">
        <p><strong>Name:</strong> {{ $case->complaint->name }}</p>
        <p><strong>Phone:</strong> {{ $case->complaint->phone_number }}</p>
        <p><strong>Statement:</strong></p>
        {!! $case->complaint->statement !!}
    </div>
</div>
