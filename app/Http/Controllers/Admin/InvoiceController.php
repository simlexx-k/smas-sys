<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        $user = auth()->user();
        
        // Log the authorization details
        Log::info('Invoice download authorization check', [
            'user_id' => $user->id,
            'user_tenant_id' => $user->tenant_id,
            'invoice_tenant_id' => $invoice->tenant_id,
            'is_landlord' => $user->hasRole('landlord'),
            'invoice_id' => $invoice->id
        ]);

        // Check if user is landlord OR if invoice belongs to user's tenant
        if (!$user->hasRole('landlord') && $user->tenant_id !== $invoice->tenant_id) {
            Log::warning('Unauthorized invoice access attempt', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id
            ]);
            abort(403, 'Unauthorized access to invoice.');
        }

        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $invoice->tenant,
            'subscription' => $invoice->subscription,
            'plan' => $invoice->subscription->plan
        ]);

        return $pdf->download("invoice-{$invoice->number}.pdf");
    }

    public function generateForSubscription(Subscription $subscription)
    {
        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'number' => Invoice::generateNumber(),
            'amount' => $subscription->price,
            'status' => 'pending',
            'billing_period_start' => $subscription->starts_at,
            'billing_period_end' => $subscription->ends_at,
            'due_date' => now()->addDays(30),
            'payment_method' => null,
            'subtotal' => $subscription->price,
            'tax_rate' => config('billing.tax_rate', 0),
            'tax_amount' => $subscription->price * (config('billing.tax_rate', 0) / 100),
            'total' => $subscription->price * (1 + config('billing.tax_rate', 0) / 100),
        ]);

        return $invoice;
    }
} 