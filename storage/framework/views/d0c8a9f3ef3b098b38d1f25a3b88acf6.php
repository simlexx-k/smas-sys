<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Academic Report Card</title>
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
            background: #fff;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
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
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: none;
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
            width: <?php echo e($summary['percentage']); ?>%"></div>
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
            background: #fff;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }
        .details-table td {
            padding: 3px 6px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
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
            opacity: 0.05;
            transform: rotate(-30deg);
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            pointer-events: none;
            z-index: -1;
        }
        .watermark img {
            max-width: 300px;
            height: auto;
        }
        .watermark-text {
            font-size: 100px;
            color: #2c3e50;
            text-transform: uppercase;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 9px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            background: white;
            margin-top: 10px;
        }
        .school-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        .school-logo {
            max-width: 50px;
            height: auto;
            margin: 0 auto 5px auto;
            display: block;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .school-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 6px;
            font-style: italic;
            line-height: 1.3;
        }

        .school-contact {
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .student-info {
            margin: 10px 0;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
        }

        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .subject-table th, .subject-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            text-align: left;
            font-size: 11px;
        }

        .subject-table th {
            background-color: #2c3e50;
            color: white;
        }

        .summary-section {
            margin: 10px 0;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .summary-section h3 {
            margin: 5px 0;
            font-size: 12px;
        }

        .signature-section {
            position: fixed;
            bottom: 60px;
            left: 0;
            right: 0;
            width: 100%;
            padding: 0 15px;
            background: white;
            margin-top: 10px;
        }

        .signature-box {
            display: inline-block;
            width: 30%;
            margin: 0 1.5%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 11px;
        }

        .signature-title {
            margin-top: 5px;
            font-size: 10px;
            color: #666;
        }

        .container {
            padding: 10px;
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 100px;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .exam-header {
            text-align: center;
            margin: 10px 0;
        }

        .exam-header h2 {
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 8px 0;
            font-weight: bold;
        }

        .default-logo {
            width: 50px;
            height: 50px;
            margin: 0 auto 5px auto;
            background-color: #3182ce;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .graphs-container {
            display: flex;
            justify-content: center;
            margin: 10px 0;
            width: 100%;
        }

        .graph-box {
            width: 100%;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px;
            height: 180px;
        }

        .line-chart {
            position: relative;
            height: 130px;
            margin: 5px 0;
            padding: 0 10px 20px 35px;
        }

        .chart-grid {
            position: absolute;
            top: 0;
            left: 35px;
            right: 10px;
            bottom: 20px;
            border-left: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .grid-line {
            position: absolute;
            left: 0;
            right: 0;
            border-top: 1px dashed #e2e8f0;
        }

        .grid-label {
            position: absolute;
            left: -30px;
            transform: translateY(-50%);
            font-size: 8px;
            color: #718096;
        }

        .chart-line {
            position: absolute;
            left: 35px;
            right: 10px;
            bottom: 20px;
            height: calc(100% - 20px);
        }

        .line-path {
            fill: none;
            stroke: #3182ce;
            stroke-width: 2;
        }

        .line-point {
            position: absolute;
            width: 6px;
            height: 6px;
            background: white;
            border: 2px solid #3182ce;
            border-radius: 50%;
            transform: translate(-50%, 50%);
            z-index: 2;
        }

        .point-value {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            background: white;
            padding: 1px 3px;
            border-radius: 2px;
            color: #2d3748;
            font-weight: bold;
            white-space: nowrap;
        }

        .x-label {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            color: #4a5568;
            white-space: nowrap;
        }

        .graph-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            color: #2d3748;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Make remarks column wider to accommodate the text */
        .subject-table th:last-child,
        .subject-table td:last-child {
            width: 40%;
        }

        /* Ensure remarks text wraps properly */
        .subject-table td:last-child {
            white-space: normal;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    <div class="watermark">
        <?php if(isset($student_details['school_logo']) && $student_details['school_logo']): ?>
            <img src="<?php echo e($student_details['school_logo']); ?>" alt="School Logo">
        <?php else: ?>
            <div class="watermark-text">
                <?php echo e(strtoupper(substr($student_details['school_name'], 0, 2))); ?>

            </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <!-- School Header -->
        <div class="school-header">
            <?php if(isset($student_details['school_logo']) && $student_details['school_logo']): ?>
                <img src="<?php echo e($student_details['school_logo']); ?>" alt="School Logo" class="school-logo">
            <?php else: ?>
                <div class="default-logo">
                    <?php echo e(strtoupper(substr($student_details['school_name'], 0, 2))); ?>

                </div>
            <?php endif; ?>
            
            <div class="school-name">
                <?php echo e($student_details['school_name']); ?>

            </div>
            
            <?php if(isset($student_details['school_address']) && $student_details['school_address']): ?>
                <div class="school-address">
                    <?php echo e($student_details['school_address']); ?>

                </div>
            <?php endif; ?>
            
            <div class="school-contact">
                <?php if(isset($student_details['school_phone']) && $student_details['school_phone']): ?>
                    Tel: <?php echo e($student_details['school_phone']); ?>

                <?php endif; ?>
                
                <?php if(isset($student_details['school_email']) && $student_details['school_email']): ?>
                    <?php if(isset($student_details['school_phone']) && $student_details['school_phone']): ?>
                        |
                    <?php endif; ?>
                    Email: <?php echo e($student_details['school_email']); ?>

                <?php endif; ?>
            </div>
        </div>

        <!-- Exam Details -->
        <div class="exam-header">
            <h2><?php echo e($exam_details['name']); ?> - <?php echo e($exam_details['term']); ?> <?php echo e($exam_details['year']); ?></h2>
        </div>

        <!-- Existing Report Card Content -->
        <div class="report-title">STUDENT REPORT CARD</div>
        
        <!-- Student Information -->
        <div class="student-info">
            <table class="details-table">
                <tr>
                    <td><strong>Student Name:</strong></td>
                    <td><?php echo e($student_details['name']); ?></td>
                    <td><strong>Admission No:</strong></td>
                    <td><?php echo e($student_details['admission_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Class:</strong></td>
                    <td><?php echo e($student_details['class']); ?></td>
                    <td><strong>Position:</strong></td>
                    <td><?php echo e($summary['rank']); ?> out of <?php echo e($summary['total_subjects']); ?></td>
                </tr>
            </table>
        </div>

        <table class="subject-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($subject['name']); ?></td>
                    <td><?php echo e($subject['score']); ?></td>
                    <td><?php echo e($subject['grade']); ?></td>
                    <td><?php echo e($subject['remarks']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="summary-section">
            <h3>Performance Summary</h3>
            <table class="details-table">
                <tr>
                    <td><strong>Total Score:</strong></td>
                    <td><?php echo e($summary['total_score']); ?></td>
                    <td><strong>Average Score:</strong></td>
                    <td><?php echo e($summary['average_score']); ?>%</td>
                </tr>
                <tr>
                    <td><strong>Subjects Taken:</strong></td>
                    <td><?php echo e($summary['total_subjects']); ?></td>
                    <td><strong>Overall Grade:</strong></td>
                    <td><?php echo e($summary['overall_grade']); ?></td>
                </tr>
            </table>
        </div>

        <div class="graphs-container">
            <div class="graph-box">
                <div class="graph-title">Subject Performance</div>
                <div class="line-chart">
                    <div class="chart-grid">
                        <?php for($i = 0; $i <= 4; $i++): ?>
                            <div class="grid-line" style="top: <?php echo e($i * 25); ?>%">
                                <span class="grid-label"><?php echo e(100 - ($i * 25)); ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="chart-line">
                        <?php
                            $subjects = collect($subjects);
                            $totalPoints = $subjects->count();
                            $spacing = $totalPoints > 1 ? (100 / ($totalPoints - 1)) : 50;
                            
                            // Generate SVG path
                            $pathData = '';
                            foreach($subjects as $index => $subject) {
                                $x = $totalPoints > 1 ? ($index * $spacing) : 50;
                                $y = 100 - floatval($subject['score']);
                                if ($index === 0) {
                                    $pathData .= "M {$x} {$y}";
                                } else {
                                    $pathData .= " L {$x} {$y}";
                                }
                            }
                        ?>

                        <svg style="position: absolute; width: 100%; height: 100%; left: 0; top: 0;">
                            <path d="<?php echo e($pathData); ?>" class="line-path" />
                        </svg>

                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $x = $totalPoints > 1 ? ($index * $spacing) : 50;
                                $y = floatval($subject['score']);
                            ?>
                            <div class="line-point" style="left: <?php echo e($x); ?>%; bottom: <?php echo e($y); ?>%">
                                <span class="point-value"><?php echo e($y); ?>%</span>
                            </div>
                            <div class="x-label" style="left: <?php echo e($x); ?>%">
                                <?php echo e(substr($subject['name'], 0, 3)); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Class Teacher</div>
            <div class="signature-title">Name & Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Principal</div>
            <div class="signature-title">Name & Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Parent/Guardian</div>
            <div class="signature-title">Name & Signature</div>
        </div>
    </div>

    <div class="footer">
        <p>Generated on <?php echo e(date('F j, Y')); ?></p>
        <p>This is an official document from <?php echo e($student_details['school_name']); ?></p>
    </div>
</body>
</html><?php /**PATH C:\Projects\smas-sys\resources\views/pdf/report_card.blade.php ENDPATH**/ ?>