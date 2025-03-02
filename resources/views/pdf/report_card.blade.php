<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Academic Report</title>
    <style>
        @page { margin: 50px 40px 100px 40px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2c3e50;
            line-height: 1.6;
        }
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }
        .header-section h1 {
            color: #2c3e50;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        .student-details {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .detail-item {
            width: 48%;
            padding: 10px;
        }
        .detail-item strong {
            color: #7f8c8d;
            display: block;
            margin-bottom: 5px;
        }
        .grades-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: collapse;
        }
        .grades-table th {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: left;
            border: 1px solid #2c3e50;
        }
        .grades-table td {
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        .final-grade {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: #2c3e50;
            color: white;
            border-radius: 8px;
        }
        .watermark {
            position: fixed;
            opacity: 0.1;
            font-size: 120px;
            transform: rotate(-30deg);
            left: 100px;
            top: 300px;
            color: #2c3e50;
            pointer-events: none;
        }
        .footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 70px;
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL</div>

    <div class="header-section">
        <h1>Academic Performance Report</h1>
    </div>

    <div class="student-details">
        <div class="detail-row">
            <div class="detail-item">
                <strong>Student Name</strong>
                {{ $student }}
            </div>
            <div class="detail-item">
                <strong>Examination</strong>
                {{ $exam }}
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <strong>Subject</strong>
                {{ $subject }}
            </div>
            <div class="detail-item">
                <strong>Report Date</strong>
                {{ date('F j, Y') }}
            </div>
        </div>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>Assessment Criteria</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Final Examination</td>
                <td>{{ $score }}</td>
                <td>{{ $grade }}</td>
                <td>{{ $remarks }}</td>
            </tr>
            <!-- Add more rows as needed -->
        </tbody>
    </table>

    <div class="final-grade">
        <h3>Overall Grade: {{ $grade }}</h3>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} - Official Academic Record</p>
        <p>This document is system-generated and requires no signature</p>
    </div>
</body>
</html>