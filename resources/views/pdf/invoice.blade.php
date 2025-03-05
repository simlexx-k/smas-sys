<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        :root {
            --primary-color: #3B82F6;
            --secondary-color: #64748B;
            --success-color: #10B981;
            --warning-color: #F59E0B;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1E293B;
            line-height: 1.6;
            background: #F8FAFC;
        }
        .invoice-box {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 30px;
            border-bottom: 2px solid #E2E8F0;
            margin-bottom: 30px;
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logo {
            max-width: 160px;
            height: auto;
        }
        .company-info {
            color: var(--secondary-color);
        }
        .company-info h2 {
            color: var(--primary-color);
            margin: 0;
            font-size: 24px;
        }
        .invoice-meta {
            background: #F1F5F9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .invoice-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .invoice-meta strong {
            color: var(--secondary-color);
            display: block;
            font-weight: 500;
            margin-bottom: 4px;
            font-size: 14px;
        }
        .invoice-meta p {
            margin: 0;
            font-size: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #E2E8F0;
        }
        .items-table tr:nth-child(even) {
            background: #F8FAFC;
        }
        .total-section {
            background: #F1F5F9;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            margin-left: auto;
            margin-top: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .total-row.total {
            font-weight: 600;
            font-size: 16px;
            border-top: 2px solid var(--primary-color);
            padding-top: 12px;
            margin-top: 12px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #E2E8F0;
            color: var(--secondary-color);
            font-size: 13px;
        }
        .status {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-paid {
            background: #D1FAE5;
            color: var(--success-color);
        }
        .status-pending {
            background: #FEF3C7;
            color: var(--warning-color);
        }
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #FEFCE8;
            border-left: 4px solid #FACC15;
            border-radius: 4px;
        }
        .payment-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <img src="{{ asset('images/logo.png') }}" class="logo" alt="Company Logo">
                <div class="company-info">
                    <h2>SMAS</h2>
                    <p>School Management System<br>
                    123 Education Street<br>
                    Tech City, TC 12345<br>
                    support@smas.com</p>
                </div>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="invoice-meta-grid">
                <div>
                    <strong>Bill To</strong>
                    <p>{{ $tenant->name }}<br>
                    {{ $tenant->address }}<br>
                    {{ $tenant->email }}<br>
                    {{ $tenant->phone }}</p>
                </div>
                <div>
                    <strong>Invoice Number</strong>
                    <p>{{ $invoice->number }}</p>
                    
                    <strong>Invoice Date</strong>
                    <p>{{ $invoice->created_at->format('F j, Y') }}</p>
                    
                    <strong>Due Date</strong>
                    <p>{{ $invoice->due_date->format('F j, Y') }}</p>
                    
                    <strong>Status</strong>
                    <p><span class="status status-{{ strtolower($invoice->status) }}">
                        {{ ucfirst($invoice->status) }}
                    </span></p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Period</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $plan->name }} Subscription</strong><br>
                        <small>{{ $plan->description }}</small>
                    </td>
                    <td>
                        {{ $invoice->billing_period_start->format('M j, Y') }} -<br>
                        {{ $invoice->billing_period_end->format('M j, Y') }}
                    </td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="total-row">
                <span>Tax ({{ $invoice->tax_rate }}%):</span>
                <span>${{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row total">
                <span>Total Due:</span>
                <span>${{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if($invoice->payment_method)
                <div class="payment-info">
                    <div>
                        <strong>Payment Method</strong>
                        <p>{{ $invoice->payment_method }}</p>
                    </div>
                    <div>
                        <strong>Payment Instructions</strong>
                        <p>Bank Transfer:<br>
                        Account: 1234 5678 9012<br>
                        Routing: 021000021</p>
                    </div>
                </div>
            @endif
            
            @if($invoice->notes)
                <div class="notes-section">
                    <strong>Notes:</strong>
                    <p>{{ $invoice->notes }}</p>
                </div>
            @endif

            <p style="margin-top: 25px; text-align: center;">
                Need help? Contact support@smas.com<br>
                &copy; {{ date('Y') }} SMAS. All rights reserved.<br>
                Reg. No: 123-456-789 | Tax ID: 987654321
            </p>
        </div>
    </div>
</body>
</html>