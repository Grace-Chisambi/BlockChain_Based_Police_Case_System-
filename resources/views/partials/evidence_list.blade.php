<!-- resources/views/partials/evidence_list.blade.php -->
<h4 class="mt-4">Evidence</h4>
@forelse ($case->evidence as $evidence)
    <div class="card mt-2">
        <div class="card-body">
            <p><strong>Description:</strong> {{ $evidence->description }}</p>
            @if ($evidence->file_path)
                <p><strong>File:</strong>
                    <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank">View File</a>
                </p>
            @endif
        </div>
    </div>
@empty
    <p>No evidence uploaded yet.</p>
@endforelse
