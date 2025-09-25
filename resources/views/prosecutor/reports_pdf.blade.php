<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prosecutor Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Prosecutor Report Summary</h2>

    <table>
        <tr>
            <th>Total Cases Assigned</th>
            <td>{{ $totalCases }}</td>
        </tr>
        <tr>
            <th>Cases Closed</th>
            <td>{{ $closedCases }}</td>
        </tr>
        <tr>
            <th>Total Evidence Items</th>
            <td>{{ $totalEvidence }}</td>
        </tr>
        <tr>
            <th>Evidence Reviewed</th>
            <td>{{ $reviewedEvidence }}</td>
        </tr>
        <tr>
            <th>Approved Evidence</th>
            <td>{{ $approvedEvidence }}</td>
        </tr>
        <tr>
            <th>Rejected Evidence</th>
            <td>{{ $rejectedEvidence }}</td>
        </tr>
        <tr>
            <th>Pending Evidence</th>
            <td>{{ $totalEvidence - $reviewedEvidence }}</td>
        </tr>
    </table>

    <p style="margin-top: 40px;">Generated on {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
