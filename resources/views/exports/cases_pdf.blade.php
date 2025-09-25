<!DOCTYPE html>
<html>
<head>
    <title>Cases Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #444; padding: 5px; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Cases Report - {{ now()->format('d M Y') }}</h2>
    <table>
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Status</th>
                <th>Description</th>
                <th>Assigned</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
            <tr>
                <td>{{ $case->case_number }}</td>
                <td>{{ $case->case_status }}</td>
                <td>{{ Str::limit($case->case_description, 40) }}</td>
                <td>
                    @foreach($case->assignments as $a)
                        {{ $a->user->name }},
                    @endforeach
                </td>
                <td>{{ $case->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
