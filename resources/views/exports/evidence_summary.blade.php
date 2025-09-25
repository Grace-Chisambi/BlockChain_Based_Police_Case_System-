<h2>Reviewed Evidence - Case #{{ $case->case_id }}</h2>

@foreach ($case->evidence as $evidence)
    <hr>
    <p><strong>Description:</strong> {{ $evidence->description }}</p>
    <p><strong>Status:</strong> {{ $evidence->review_status }}</p>
    <p><strong>Reviewed By:</strong> {{ $evidence->staff->name ?? 'N/A' }}</p>
    <p><strong>Comment:</strong> {{ $evidence->review_comment }}</p>
    <p><strong>Reviewed On:</strong> {{ $evidence->reviewed_at }}</p>
@endforeach
