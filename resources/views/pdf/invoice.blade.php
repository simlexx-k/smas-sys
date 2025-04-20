<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->number }}</title>
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
            @if(config('app.logo'))
                <img src="{{ config('app.logo') }}" style="height: 48px; margin-bottom: 1rem;" alt="Logo">
            @endif
            <div style="color: var(--secondary); font-size: 0.875rem; margin-top: 0.5rem;">
                {!! nl2br(e(config('app.address'))) !!}<br>
                VAT: {{ config('app.vat_number') }}<br>
                Tel: {{ config('app.phone') }}
            </div>
        </div>

        <div class="invoice-meta">
            <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem; color: var(--accent);">INVOICE</h1>
            <div style="font-size: 0.875rem;">
                <div style="margin-bottom: 0.25rem;">
                    <span style="color: var(--secondary);">Number:</span> 
                    <strong>{{ $invoice->number }}</strong>
                </div>
                <div style="margin-bottom: 0.25rem;">
                    <span style="color: var(--secondary);">Issued:</span> 
                    {{ $invoice->created_at->format('M j, Y') }}
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <span style="color: var(--secondary);">Due:</span> 
                    <span style="color: {{ $invoice->isOverdue ? 'var(--danger)' : 'inherit' }};">
                        {{ $invoice->due_date->format('M j, Y') }}
                    </span>
                </div>
                <span class="badge" style="background-color: {{ $invoice->status === 'paid' ? '#DCFCE7' : '#FEE2E2' }}; color: {{ $invoice->status === 'paid' ? '#166534' : '#991B1B' }};">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <div style="font-size: 0.875rem; margin-bottom: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: var(--accent);">Bill to</h3>
                <div style="line-height: 1.6;">
                    <strong>{{ $tenant->name }}</strong><br>
                    @if($tenant->address)
                        {!! nl2br(e($tenant->address)) !!}
                    @else
                        <span class="text-danger">No billing address provided</span>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div style="font-size: 0.875rem;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: var(--accent);">Subscription Details</h3>
                <div style="line-height: 1.6;">
                    <div><strong>Plan:</strong> {{ $subscription->plan->name }}</div>
                    <div><strong>Billing Cycle:</strong> {{ ucfirst($subscription->plan->billing_period) }}</div>
                    <div><strong>Price:</strong> @currency($subscription->plan->price)</div>
                    @if($subscription->trial_ends_at)
                        <div><strong>Trial Ends:</strong> {{ $subscription->trial_ends_at->format('M j, Y') }}</div>
                    @endif
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
            @forelse($invoice->items as $item)
            <tr>
                <td>
                    {{ $subscription->plan->name }} - 
                    {{ $invoice->billing_period_start->format('M Y') }}
                    @if($subscription->plan->billing_period)
                        ({{ ucfirst($subscription->plan->billing_period) }})
                    @endif
                </td>
                <td class="text-right">1.00</td>
                <td class="text-right currency">@currency($subscription->plan->price)</td>
                <td class="text-right">{{ number_format($invoice->tax_rate, 1) }}%</td>
                <td class="text-right currency">@currency($subscription->plan->price)</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-danger" style="padding: 1.5rem;">No items found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-card">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Subtotal:</span>
            <span class="currency">@currency($invoice->subtotal)</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Tax ({{ number_format($invoice->tax_rate, 1) }}%):</span>
            <span class="currency">@currency($invoice->tax_amount)</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: 600; padding-top: 0.75rem; border-top: 1px solid var(--border);">
            <span>Total Due:</span>
            <span class="currency">@currency($invoice->total)</span>
        </div>
    </div>

    <div class="footer">
        <div style="margin-bottom: 1rem;">
            <h4 style="margin: 0 0 0.5rem 0; color: var(--accent);">Payment Instructions</h4>
            <div style="white-space: pre-line;">{!! nl2br(e(config('app.payment_instructions'))) !!}</div>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            {{ config('app.name') }} is a registered company in Kenya • 
            All amounts in {{ config('app.currency_name') }} ({{ config('app.currency_symbol') }}) • 
            Invoice generated: {{ now()->format('M j, Y H:i') }}
        </div>
    </div>
</body>
</html>