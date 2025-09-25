@if(empty($casesWithEvidence) || count($casesWithEvidence) === 0)
    <div class="alert alert-info">No case has evidence uploaded yet.</div>
@else
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Type</th>
                <th>Status</th>
                <th>Evidence Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($casesWithEvidence as $case)
                <tr>
                    <td>{{ $case->case_number }}</td>
                    <td>{{ $case->case_type }}</td>
                    <td>{{ $case->case_status }}</td>
                    <td>{{ $case->evidence_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
