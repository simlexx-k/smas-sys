<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nominal List</title>
    <style>
        /* Set page to landscape */
        @page {
            size: landscape;
        }

        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .header { 
            text-align: center; 
            margin-bottom: 20px;
            position: relative;
            padding-top: 10px;
        }

        .school-logo {
            position: absolute;
            left: 20px;
            top: 10px;
            max-width: 80px;
            max-height: 80px;
        }

        .school-details {
            margin: 0 100px; /* Make space for logo */
        }

        .header h1 { 
            color: #2c3e50; 
            margin: 0; 
            font-size: 18px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .school-contact {
            font-size: 11px;
            color: #666;
            margin: 5px 0;
        }

        .exam-details {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #34495e;
        }

        .class-info {
            margin: 15px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }

        .class-info strong {
            color: #2c3e50;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }

        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: center; 
            font-size: 11px;
        }

        th { 
            background-color: #f8f9fa; 
            font-weight: bold;
        }

        .student-name {
            text-align: left;
            white-space: nowrap;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            /* font-weight: 500; */
        }

        .total-column {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .footer { 
            text-align: center; 
            margin-top: 20px; 
            font-size: 10px; 
            color: #666; 
        }

        .subject-legend {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .subject-legend h4 {
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .legend-table td {
            font-size: 11px;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rank-column {
            font-weight: bold;
            text-align: center;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if($tenant->logo_url): ?>
            <img src="<?php echo e($tenant->logo_url); ?>" alt="School Logo" class="school-logo">
        <?php endif; ?>
        
        <div class="school-details">
            <h1><?php echo e($tenant->name ?? 'School Name'); ?></h1>
            <div class="school-contact">
                <?php if($tenant->address): ?>
                    <?php echo e($tenant->address); ?><br>
                <?php endif; ?>
                <?php if($tenant->phone): ?>
                    Tel: <?php echo e($tenant->phone); ?>

                <?php endif; ?>
                <?php if($tenant->email): ?>
                    | Email: <?php echo e($tenant->email); ?>

                <?php endif; ?>
                <?php if($tenant->website): ?>
                    | <?php echo e($tenant->website); ?>

                <?php endif; ?>
            </div>
            <div class="exam-details">
                <?php echo e($exam->name ?? 'Exam'); ?> Results
            </div>
        </div>
    </div>

    <div class="class-info">
        <strong>Class:</strong> <?php echo e($class->name ?? 'N/A'); ?> |
        <strong>Term:</strong> <?php echo e($exam->term ?? 'N/A'); ?> |
        <strong>Academic Year:</strong> <?php echo e($exam->year ?? date('Y')); ?> |
        <strong>Total Students:</strong> <?php echo e(count($sortedStudents)); ?>

    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Rank</th>
                <th style="width: 200px;">Student Name</th>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th title="<?php echo e($subject->name); ?>"><?php echo e($subject->code); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th class="total-column">Total</th>
                <th class="total-column">Avg</th>
                <th class="total-column">Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $sortedStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="rank-column"><?php echo e($positions[$student->id] ?? '-'); ?></td>
                    <td class="student-name"><?php echo e(strtoupper($student->full_name)); ?></td>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $score = $scores[$student->id][$subject->id] ?? '-';
                        ?>
                        <td><?php echo e($score); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="total-column"><?php echo e($totals[$student->id] ?? '-'); ?></td>
                    <td class="total-column"><?php echo e($averages[$student->id] ?? '-'); ?></td>
                    <td class="total-column"><?php echo e($grades[$student->id] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="subject-legend">
        <h4>Subject Legend:</h4>
        <table class="legend-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <?php $__currentLoopData = $subjects->chunk(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectChunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $subjectChunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td style="border: 1px solid #ddd; padding: 5px; width: 25%;">
                            <strong><?php echo e($subject->code); ?></strong>: <?php echo e($subject->name); ?>

                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($subjectChunk->count() < 4): ?>
                        <?php for($i = 0; $i < 4 - $subjectChunk->count(); $i++): ?>
                            <td style="border: 1px solid #ddd; padding: 5px; width: 25%;"></td>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tr><tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </table>
    </div>

    <div class="grade-key" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd;">
        <h4 style="margin-bottom: 10px; font-weight: bold;">Grade Descriptors:</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr>
                <th style="border: 1px solid #ddd; padding: 5px; text-align: left;">Grade</th>
                <th style="border: 1px solid #ddd; padding: 5px; text-align: left;">Description</th>
                <th style="border: 1px solid #ddd; padding: 5px; text-align: left;">Marks Range</th>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 5px;">EE</td>
                <td style="border: 1px solid #ddd; padding: 5px;">Exceeding Expectation</td>
                <td style="border: 1px solid #ddd; padding: 5px;">76-100</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 5px;">ME</td>
                <td style="border: 1px solid #ddd; padding: 5px;">Meeting Expectation</td>
                <td style="border: 1px solid #ddd; padding: 5px;">51-75</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 5px;">AE</td>
                <td style="border: 1px solid #ddd; padding: 5px;">Approaching Expectation</td>
                <td style="border: 1px solid #ddd; padding: 5px;">26-50</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ddd; padding: 5px;">BE</td>
                <td style="border: 1px solid #ddd; padding: 5px;">Below Expectation</td>
                <td style="border: 1px solid #ddd; padding: 5px;">0-25</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated on <?php echo e(now()->format('Y-m-d H:i')); ?></p>
    </div>
</body>
</html>
<?php /**PATH C:\Projects\smas-sys\resources\views/pdf/nominal_list.blade.php ENDPATH**/ ?>