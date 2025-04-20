<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice <?php echo e($invoice->number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1E293B;
            --secondary: #64748B;
            --accent: #6366F1;
            --success: #16A34A;
            --danger: #DC2626;
            --border: #E2E8F0;
            --bg-light: #F8FAFC;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--primary);
            line-height: 1.5;
            margin: 0;
            padding: 40px 50px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2.5rem;
        }

        .company-info {
            max-width: 300px;
        }

        .invoice-meta {
            text-align: right;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin: 2rem 0;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.8rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }

        .table th {
            background: var(--bg-light);
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .total-card {
            background: var(--bg-light);
            border-radius: 8px;
            padding: 1.5rem;
            max-width: 320px;
            margin-left: auto;
        }

        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            font-size: 0.875rem;
            color: var(--secondary);
        }

        .text-right {
            text-align: right;
        }

        .text-danger {
            color: var(--danger);
        }

        .text-success {
            color: var(--success);
        }

        .currency {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <?php if(config('app.logo')): ?>
                <img src="<?php echo e(config('app.logo')); ?>" style="height: 48px; margin-bottom: 1rem;" alt="Logo">
            <?php endif; ?>
            <div style="color: var(--secondary); font-size: 0.875rem; margin-top: 0.5rem;">
                <?php echo nl2br(e(config('app.address'))); ?><br>
                VAT: <?php echo e(config('app.vat_number')); ?><br>
                Tel: <?php echo e(config('app.phone')); ?>

            </div>
        </div>

        <div class="invoice-meta">
            <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem; color: var(--accent);">INVOICE</h1>
            <div style="font-size: 0.875rem;">
                <div style="margin-bottom: 0.25rem;">
                    <span style="color: var(--secondary);">Number:</span> 
                    <strong><?php echo e($invoice->number); ?></strong>
                </div>
                <div style="margin-bottom: 0.25rem;">
                    <span style="color: var(--secondary);">Issued:</span> 
                    <?php echo e($invoice->created_at->format('M j, Y')); ?>

                </div>
                <div style="margin-bottom: 0.5rem;">
                    <span style="color: var(--secondary);">Due:</span> 
                    <span style="color: <?php echo e($invoice->isOverdue ? 'var(--danger)' : 'inherit'); ?>;">
                        <?php echo e($invoice->due_date->format('M j, Y')); ?>

                    </span>
                </div>
                <span class="badge" style="background-color: <?php echo e($invoice->status === 'paid' ? '#DCFCE7' : '#FEE2E2'); ?>; color: <?php echo e($invoice->status === 'paid' ? '#166534' : '#991B1B'); ?>;">
                    <?php echo e(strtoupper($invoice->status)); ?>

                </span>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <div style="font-size: 0.875rem; margin-bottom: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: var(--accent);">Bill to</h3>
                <div style="line-height: 1.6;">
                    <strong><?php echo e($tenant->name); ?></strong><br>
                    <?php if($tenant->address): ?>
                        <?php echo nl2br(e($tenant->address)); ?>

                    <?php else: ?>
                        <span class="text-danger">No billing address provided</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <div style="font-size: 0.875rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: var(--accent);">Subscription Details</h3>
                <div style="line-height: 1.6;">
                    <div><strong>Plan:</strong> <?php echo e($subscription->plan->name); ?></div>
                    <div><strong>Billing Cycle:</strong> <?php echo e(ucfirst($subscription->plan->billing_period)); ?></div>
                    <div><strong>Price:</strong> <?php echo config('app.currency_symbol') . number_format($subscription->plan->price, 2); ?></div>
                    <?php if($subscription->trial_ends_at): ?>
                        <div><strong>Trial Ends:</strong> <?php echo e($subscription->trial_ends_at->format('M j, Y')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <?php echo e($subscription->plan->name); ?> - 
                    <?php echo e($invoice->billing_period_start->format('M Y')); ?>

                    <?php if($subscription->plan->billing_period): ?>
                        (<?php echo e(ucfirst($subscription->plan->billing_period)); ?>)
                    <?php endif; ?>
                </td>
                <td class="text-right">1.00</td>
                <td class="text-right currency"><?php echo config('app.currency_symbol') . number_format($subscription->plan->price, 2); ?></td>
                <td class="text-right"><?php echo e(number_format($invoice->tax_rate, 1)); ?>%</td>
                <td class="text-right currency"><?php echo config('app.currency_symbol') . number_format($subscription->plan->price, 2); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center text-danger" style="padding: 1.5rem;">No items found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-card">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Subtotal:</span>
            <span class="currency"><?php echo config('app.currency_symbol') . number_format($invoice->subtotal, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Tax (<?php echo e(number_format($invoice->tax_rate, 1)); ?>%):</span>
            <span class="currency"><?php echo config('app.currency_symbol') . number_format($invoice->tax_amount, 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: 600; padding-top: 0.75rem; border-top: 1px solid var(--border);">
            <span>Total Due:</span>
            <span class="currency"><?php echo config('app.currency_symbol') . number_format($invoice->total, 2); ?></span>
        </div>
    </div>

    <div class="footer">
        <div style="margin-bottom: 1rem;">
            <h4 style="margin: 0 0 0.5rem 0; color: var(--accent);">Payment Instructions</h4>
            <div style="white-space: pre-line;"><?php echo nl2br(e(config('app.payment_instructions'))); ?></div>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <?php echo e(config('app.name')); ?> is a registered company in Kenya • 
            All amounts in <?php echo e(config('app.currency_name')); ?> (<?php echo e(config('app.currency_symbol')); ?>) • 
            Invoice generated: <?php echo e(now()->format('M j, Y H:i')); ?>

        </div>
    </div>
</body>
</html><?php /**PATH C:\Projects\smas-sys\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>