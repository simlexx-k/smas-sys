<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nominal List</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; }
        .header h1 { color: #2c3e50; margin: 0; }
        .header h2 { color: #34495e; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #2c3e50; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #f1f1f1; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tenant->name ?? 'Unknown School' }}</h1>
        <h2>Nominal List for {{ $exam->name ?? 'Unknown Exam' }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportCards as $reportCard)
                <tr>
                    <td>{{ $reportCard->student->name }}</td>
                    <td>{{ $reportCard->subject->name }}</td>
                    <td>{{ $reportCard->score }}</td>
                    <td>{{ $reportCard->grade }}</td>
                    <td>{{ $reportCard->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
