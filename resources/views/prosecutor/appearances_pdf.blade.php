<!DOCTYPE html>
<html>
<head>
    <title>Court Appearances PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem;}
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Court Appearances</h2>
    <table>
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Date</th>
                <th>Time</th>
                <th>Court</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cases as $case)
                <tr>
                    <td>{{ $case->case_number }}</td>
                    <td>{{ $case->date }}</td>
                    <td>{{ $case->time }}</td>
                    <td>{{ $case->court_name }}</td>
                    <td>{{ $case->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
