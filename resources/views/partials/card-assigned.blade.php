@if(empty($assignedCases) || count($assignedCases) === 0)
    <div class="alert alert-info">No cases have been assigned yet.</div>
@else
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Investigator</th>
                <th>Assigned At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignedCases as $assignment)
                <tr>
                    <td>{{ $assignment->case_number }}</td>
                    <td>{{ $assignment->fname }} {{ $assignment->sname }}</td>
                    <td>{{ \Carbon\Carbon::parse($assignment->assigned_at)->diffForHumans() }}</td>
                    <td>
                        {{-- Link to the general progress page (all assigned cases) --}}
                        <a href="{{ route('progress.index') }}"
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-tasks me-1"></i> View Cases Progress
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
