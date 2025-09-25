<!-- resources/views/partials/case_info.blade.php -->
<div class="card">
    <div class="card-header">
        Case Information
    </div>
    <div class="card-body">
        <p><strong>Status:</strong> {{ $case->case_status }}</p>
        <p><strong>Description:</strong> {{ $case->case_description }}</p>
        <p><strong>Created At:</strong> {{ $case->created_at->format('d M Y') }}</p>
    </div>
</div>
