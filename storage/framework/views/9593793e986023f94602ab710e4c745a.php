<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Report</title>
    <style>
        @page {
            size: landscape;
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            border-bottom: 2px solid #333;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: center;
        }
        .logo-container {
            text-align: left;
        }
        .logo {
            max-width: 100px;
            max-height: 100px;
        }
        .school-details {
            text-align: center;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .school-contacts {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .school-address {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .report-info {
            text-align: right;
            font-size: 12px;
        }
        .report-title {
            font-size: 20px;
            margin: 15px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .class-info {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .stats-container {
            margin: 15px 0;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .stats-row {
            display: table-row;
        }
        .stat-box {
            display: table-cell;
            padding: 8px 15px;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background-color: #f8f9fa;
        }
        .stat-box .label {
            font-size: 12px;
            color: #666;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
        .performance-header {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .grade-distribution {
            margin-bottom: 30px;
        }
        .grade-table {
            width: 50%;
            margin: 0 auto;
        }
        .subject-breakdown {
            margin-bottom: 30px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px;
        }
        .rank-cell {
            font-weight: bold;
            text-align: center;
        }
        .student-total {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .grade-key {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .grade-key table {
            width: 100%;
            margin-bottom: 0;
        }
        .grade-key th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .performance-indicator {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .ee { background-color: #d4edda; color: #155724; }
        .me { background-color: #fff3cd; color: #856404; }
        .ae { background-color: #f8d7da; color: #721c24; }
        .be { background-color: #f1f1f1; color: #5a5a5a; }
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Page 1: Main Results -->
    <div class="header">
        <div class="logo-container">
            <?php if($tenant->logo_url): ?>
                <img src="<?php echo e($tenant->logo_url); ?>" alt="School Logo" class="logo">
            <?php endif; ?>
        </div>
        
        <div class="school-details">
            <div class="school-name"><?php echo e($tenant->name); ?></div>
            <div class="school-address">
                <?php echo e($tenant->address); ?>

            </div>
            <div class="school-contacts">
                Tel: <?php echo e($tenant->phone); ?> | Email: <?php echo e($tenant->email); ?>

            </div>
            <div class="school-type">
                <?php echo e(ucfirst($tenant->school_type)); ?> School
            </div>
            <div class="report-title">
                Academic Performance Report
            </div>
            <div class="class-info">
                <?php echo e($class['name']); ?> - <?php echo e($exam['name']); ?> (<?php echo e($exam['term']); ?>)
            </div>
        </div>

        <div class="report-info">
            <div>Report Generated: <?php echo e($generated_at); ?></div>
            <?php if($tenant->settings && isset($tenant->settings['motto'])): ?>
                <div style="margin-top: 10px; font-style: italic;">
                    "<?php echo e($tenant->settings['motto']); ?>"
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Class Average</div>
            <div class="value"><?php echo e(number_format($statistics['class_average'], 1)); ?>%</div>
        </div>
        <div class="stat-box">
            <div class="label">Highest Score</div>
            <div class="value"><?php echo e($statistics['highest_score']); ?>%</div>
        </div>
        <div class="stat-box">
            <div class="label">Lowest Score</div>
            <div class="value"><?php echo e($statistics['lowest_score']); ?>%</div>
        </div>
        <div class="stat-box">
            <div class="label">Total Students</div>
            <div class="value"><?php echo e($statistics['total_students']); ?></div>
        </div>
    </div>

    <!-- Main Results Table -->
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Name</th>
                <?php $__currentLoopData = $results[0]['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($score['subject']); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th>Total</th>
                <th>Average</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $validScores = collect($result['scores'])->filter(fn($score) => !is_null($score['score']));
                    $total = $validScores->sum('score');
                    $average = $validScores->count() > 0 ? $total / $validScores->count() : 0;
                    $overallGrade = $result['overall_grade'] ?? calculateGrade($average);
                ?>
                <tr>
                    <td class="rank-cell">
                        <?php echo e($result['rank']); ?> out of <?php echo e($statistics['total_students']); ?>

                    </td>
                    <td><?php echo e($result['student_name']); ?></td>
                    <?php $__currentLoopData = $result['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td>
                            <?php if($score['score']): ?>
                                <?php echo e(number_format($score['score'], 1)); ?>%
                                <span class="performance-indicator <?php echo e(strtolower($score['grade'])); ?>">
                                    <?php echo e($score['grade']); ?>

                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="student-total"><?php echo e(number_format($total, 1)); ?></td>
                    <td class="student-total"><?php echo e(number_format($average, 1)); ?>%</td>
                    <td class="student-total">
                        <span class="performance-indicator <?php echo e(strtolower($overallGrade)); ?>">
                            <?php echo e($overallGrade); ?>

                        </span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Class Average</strong></td>
                <?php $__currentLoopData = $results[0]['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $subjectScores = collect($results)->map(function($result) use ($index) {
                            return $result['scores'][$index]['score'];
                        })->filter();
                        $subjectAvg = $subjectScores->avg() ?? 0;
                        $subjectGrade = calculateGrade($subjectAvg);
                    ?>
                    <td>
                        <?php echo e(number_format($subjectAvg, 1)); ?>%
                        <span class="performance-indicator <?php echo e(strtolower($subjectGrade)); ?>">
                            <?php echo e($subjectGrade); ?>

                        </span>
                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $overallAvg = collect($results)->avg('average');
                    $overallGrade = calculateGrade($overallAvg);
                ?>
                <td class="student-total"><?php echo e(number_format(collect($results)->sum('total_score'), 1)); ?></td>
                <td class="student-total"><?php echo e(number_format($overallAvg, 1)); ?>%</td>
                <td class="student-total">
                    <span class="performance-indicator <?php echo e(strtolower($overallGrade)); ?>">
                        <?php echo e($overallGrade); ?>

                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php
    function calculateGrade($score) {
        switch (true) {
            case $score >= 76: return 'EE';
            case $score >= 51: return 'ME';
            case $score >= 26: return 'AE';
            default: return 'BE';
        }
    }
    ?>

    <!-- Page 2: Performance Breakdown -->
    <div class="page-break"></div>
    <div class="performance-header">Performance Analysis</div>

    <!-- Grade Distribution -->
    <div class="grade-distribution">
        <h3>Grade Distribution</h3>
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Range</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $gradeRanges = [
                        'EE' => ['min' => 76, 'max' => 100, 'class' => 'ee'],
                        'ME' => ['min' => 51, 'max' => 75, 'class' => 'me'],
                        'AE' => ['min' => 26, 'max' => 50, 'class' => 'ae'],
                        'BE' => ['min' => 0, 'max' => 25, 'class' => 'be']
                    ];
                    $totalGrades = 0;
                    $gradeCounts = [];
                ?>

                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $result['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($score['score']): ?>
                            <?php
                                $totalGrades++;
                                $grade = $score['grade'];
                                $gradeCounts[$grade] = ($gradeCounts[$grade] ?? 0) + 1;
                            ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $gradeRanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade => $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <span class="performance-indicator <?php echo e($range['class']); ?>"><?php echo e($grade); ?></span>
                        </td>
                        <td><?php echo e($range['min']); ?>-<?php echo e($range['max']); ?>%</td>
                        <td><?php echo e($gradeCounts[$grade] ?? 0); ?></td>
                        <td>
                            <?php echo e($totalGrades > 0 ? number_format((($gradeCounts[$grade] ?? 0) / $totalGrades) * 100, 1) : 0); ?>%
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Subject Performance -->
    <div class="subject-breakdown">
        <h3>Subject Performance</h3>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Average</th>
                    <th>Highest</th>
                    <th>Lowest</th>
                    <th>EE</th>
                    <th>ME</th>
                    <th>AE</th>
                    <th>BE</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $results[0]['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subjectScore): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $subjectScores = collect($results)->map(function($result) use ($index) {
                            return $result['scores'][$index]['score'];
                        })->filter();
                    ?>
                    <tr>
                        <td><?php echo e($subjectScore['subject']); ?></td>
                        <td><?php echo e($subjectScores->avg() ? number_format($subjectScores->avg(), 1) : '-'); ?>%</td>
                        <td><?php echo e($subjectScores->max() ?? '-'); ?>%</td>
                        <td><?php echo e($subjectScores->min() ?? '-'); ?>%</td>
                        <td><?php echo e(collect($results)->filter(function($result) use ($index) { 
                            return $result['scores'][$index]['grade'] === 'EE';
                        })->count()); ?></td>
                        <td><?php echo e(collect($results)->filter(function($result) use ($index) { 
                            return $result['scores'][$index]['grade'] === 'ME';
                        })->count()); ?></td>
                        <td><?php echo e(collect($results)->filter(function($result) use ($index) { 
                            return $result['scores'][$index]['grade'] === 'AE';
                        })->count()); ?></td>
                        <td><?php echo e(collect($results)->filter(function($result) use ($index) { 
                            return $result['scores'][$index]['grade'] === 'BE';
                        })->count()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Class Teacher</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Principal</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Date</div>
        </div>
    </div>

    <div class="grade-key">
        <h4>Performance Indicators</h4>
        <table>
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Range</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="performance-indicator ee">EE</span></td>
                    <td>76-100%</td>
                    <td>Exceeding Expectation</td>
                </tr>
                <tr>
                    <td><span class="performance-indicator me">ME</span></td>
                    <td>51-75%</td>
                    <td>Meeting Expectation</td>
                </tr>
                <tr>
                    <td><span class="performance-indicator ae">AE</span></td>
                    <td>26-50%</td>
                    <td>Approaching Expectation</td>
                </tr>
                <tr>
                    <td><span class="performance-indicator be">BE</span></td>
                    <td>0-25%</td>
                    <td>Below Expectation</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generated on <?php echo e($generated_at); ?> | <?php echo e($tenant->name); ?>

    </div>
</body>
</html> <?php /**PATH C:\Projects\smas-sys\resources\views/pdfs/academic-report.blade.php ENDPATH**/ ?>