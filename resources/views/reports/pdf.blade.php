<!DOCTYPE html>
<html>
<head>
    <title>Cases Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Cases Report</h2>
    <p>From: {{ $from ? $from : 'All' }} | To: {{ $to ? $to : 'Now' }}</p>


    <table>
        <thead>
            <tr>
                <th>Case #</th>
                <th>Type</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
                <tr>
                    <td>{{ $case->case_number }}</td>
                    <td>{{ $case->case_type }}</td>
                    <td>{{ $case->case_status }}</td>
                    <td>{{ ucfirst($case->priority) }}</td>
                    <td>{{ $case->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
