<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice <?php echo e($invoice->number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3B82F6;
            --secondary-color: #64748B;
            --success-color: #10B981;
            --border-color: #E2E8F0;
            --accent-color: #7C3AED;
        }
        
        body {
            font-family: 'Ubuntu', sans-serif;
            color: #1E293B;
            line-height: 1.5;
            margin: 0;
            padding: 2.5cm;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .logo-section {
            width: 30%;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }
        
        .invoice-dates {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }
        
        .billing-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #F8FAFC;
            border-radius: 12px;
        }
        
        .billing-section h3 {
            font-weight: 500;
            color: var(--accent-color);
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .items-table th {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .items-table td {
            padding: 1.2rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .amount-cell {
            text-align: right;
            font-weight: 500;
        }
        
        .total-section {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
            font-size: 1rem;
        }
        
        .total-label {
            font-weight: 300;
        }
        
        .total-amount {
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .payment-instructions {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #F0FDFA;
            border-left: 4px solid var(--success-color);
            border-radius: 6px;
        }
        
        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.8rem;
            color: var(--secondary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .status-paid {
            background: var(--success-color);
            color: white;
        }
        
        .status-pending {
            background: #F59E0B;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <div class="company-info">
                <h1 style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-size: 1.8rem">
                    <?php echo e(config('app.name')); ?>

                </h1>
                <p style="margin: 0.2rem 0; font-size: 0.9rem; color: var(--secondary-color)">
                    123 Education Street<br>
                    Tech City, TC 12345<br>
                    support@smas.com<br>
                    Reg. No: 123-456-789 | Tax ID: 987654321
                </p>
            </div>
        </div>
        
        <div class="invoice-info">
            <h1 class="invoice-number">INVOICE <?php echo e($invoice->number); ?></h1>
            <div class="invoice-dates">
                <p style="margin: 0.3rem 0">
                    <strong>Issued:</strong> <?php echo e($invoice->created_at->format('M j, Y')); ?><br>
                    <strong>Due:</strong> <?php echo e($invoice->due_date->format('M j, Y')); ?>

                </p>
                <div class="status-badge status-<?php echo e(strtolower($invoice->status)); ?>">
                    <?php echo e(ucfirst($invoice->status)); ?>

                </div>
            </div>
        </div>
    </div>

    <div class="billing-grid">
        <div class="billing-section">
            <h3>From</h3>
            <p style="margin: 0.5rem 0; line-height: 1.4">
                <?php echo e(config('app.name')); ?><br>
                Reg. No: 123-456-789<br>
                Tax ID: 987654321
            </p>
        </div>
        
        <div class="billing-section">
            <h3>To</h3>
            <p style="margin: 0.5rem 0; line-height: 1.4">
                <strong><?php echo e($tenant->name); ?></strong><br>
                <?php echo e($tenant->address); ?><br>
                <?php echo e($tenant->email); ?><br>
                <?php echo e($tenant->phone); ?>

            </p>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th style="text-align: right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight: 500"><?php echo e($plan->name); ?> Subscription</div>
                    <div style="font-size: 0.9rem; color: var(--secondary-color)">
                        <?php echo e($plan->description); ?>

                    </div>
                </td>
                <td>
                    <?php echo e($invoice->billing_period_start->format('M j')); ?> - 
                    <?php echo e($invoice->billing_period_end->format('M j, Y')); ?>

                </td>
                <td class="amount-cell">$<?php echo e(number_format($invoice->subtotal, 2)); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span>$<?php echo e(number_format($invoice->subtotal, 2)); ?></span>
        </div>
        <?php if($invoice->tax_amount > 0): ?>
        <div class="total-row">
            <span class="total-label">Tax (<?php echo e($invoice->tax_rate); ?>%)</span>
            <span>$<?php echo e(number_format($invoice->tax_amount, 2)); ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row" style="margin-top: 1rem">
            <span class="total-label" style="font-weight: 600">Total Due</span>
            <span class="total-amount">$<?php echo e(number_format($invoice->total, 2)); ?></span>
        </div>
    </div>

    <div class="payment-instructions">
        <h3 style="margin: 0 0 1rem 0; font-size: 1rem">Payment Instructions</h3>
        <p style="margin: 0.5rem 0; font-size: 0.9rem">
            Bank Transfer:<br>
            <strong>Account:</strong> 1234 5678 9012<br>
            <strong>Routing:</strong> 021000021<br>
            <strong>Reference:</strong> <?php echo e($invoice->number); ?>

        </p>
    </div>

    <div class="footer">
        <p style="margin: 0.5rem 0">
            Need help? Contact support@smas.com<br>
            &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.<br>
            This is an automatically generated invoice
        </p>
    </div>
</body>
</html><?php /**PATH C:\Projects\smas-sys\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>