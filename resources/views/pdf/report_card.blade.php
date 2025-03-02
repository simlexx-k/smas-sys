<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Academic Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        @font-face {
            font-family: 'Roboto';
            src: url('https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxK.woff2') format('woff2');
        }
        body {
            font-family: 'Roboto', sans-serif;
            color: #2d3748;
            background: #f7fafc;
        }
        .header {
            position: relative;
            background: linear-gradient(135deg, #4299e1, #3182ce);
            color: white;
            padding: 2rem;
            text-align: center;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin: 1rem;
        }
        .progress-bar {
            height: 0.75rem;
            background: #e2e8f0;
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #48bb78;
            width: {{ (707/800)*100 }}%;
        }
        .icon {
            margin-right: 0.5rem;
            font-size: 1.1em;
        }
        .hover-effect:hover {
            background: #f7fafc;
            transition: background-color 0.2s;
        }
        .two-column {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        .sidebar {
            background: #edf2f7;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }
        .details-table td {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
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

    <div class="header">
        <h1 style="font-weight: 700; font-size: 2rem; margin: 0;">Academic Performance Report</h1>
    </div>

    <div class="two-column">
        <div class="card">
            <table class="details-table">
                <tr>
                    <td><i class="icon fas fa-user"></i><strong>Student Name:</strong></td>
                    <td>{{ $student }}</td>
                    <td><i class="icon fas fa-book"></i><strong>Class:</strong></td>
                    <td>{{ $class }}</td>
                </tr>
                <tr>
                    <td><i class="icon fas fa-file-alt"></i><strong>Exam:</strong></td>
                    <td>{{ $exam }}</td>
                    <td><i class="icon fas fa-calendar-alt"></i><strong>Date:</strong></td>
                    <td>{{ date('F j, Y') }}</td>
                </tr>
                <tr>
                    <td><i class="icon fas fa-trophy"></i><strong>Rank:</strong></td>
                    <td>2 out of 40</td>
                    <td><i class="icon fas fa-wallet"></i><strong>Fee Balance:</strong></td>
                    <td>Ksh [placeholder]</td>
                </tr>
            </table>
        </div>

        <div class="sidebar">
            <div class="card">
                <h3 style="font-weight: 500; margin-top: 0;">Total Score</h3>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <p style="text-align: center; margin: 0.5rem 0;">707.00 out of 800</p>
            </div>
        </div>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Teacher Comment</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $subject }}</td>
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