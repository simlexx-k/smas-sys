<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function download(Invoice $invoice)
    {
        // Add relationship loading
        $invoice->load(['subscription.plan', 'tenant']);
        
        // Verify file existence first
        if (!Storage::disk('invoices')->exists($invoice->file_path)) {
            $invoice->update([
                'generated_at' => null,
                'error' => true
            ]);
            abort(404, 'Invoice PDF not found');
        }
        
        return Storage::disk('invoices')->download(
            $invoice->file_path, 
            "invoice-{$invoice->number}.pdf",
            ['Cache-Control' => 'no-store, no-cache, must-revalidate']
        );
    }

    public function generateForSubscription(Subscription $subscription)
    {
        Log::info('Starting invoice generation for subscription', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'starts_at' => $subscription->starts_at,
            'ends_at' => $subscription->ends_at
        ]);

        if ($subscription->status !== Subscription::STATUS_ACTIVE) {
            Log::warning('Subscription not active - aborting invoice generation', [
                'subscription_id' => $subscription->id,
                'current_status' => $subscription->status
            ]);
            return;
        }

        $existingInvoices = $subscription->invoices()
            ->whereBetween('billing_period_end', [
                $subscription->starts_at,
                $subscription->ends_at
            ])
            ->exists();

        Log::debug('Existing invoices check', [
            'exists' => $existingInvoices,
            'count' => $subscription->invoices()->count(),
            'subscription_period' => $subscription->starts_at.' - '.$subscription->ends_at
        ]);

        if ($existingInvoices) {
            Log::info('Invoices already exist for this subscription period', [
                'subscription_id' => $subscription->id
            ]);
            return;
        }

        $periods = $subscription->getBillingPeriods();
        Log::info('Calculated billing periods', [
            'period_count' => count($periods),
            'first_period' => $periods[0]['start'] ?? null,
            'last_period' => end($periods)['end'] ?? null
        ]);

        foreach ($periods as $period) {
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'number' => Invoice::generateNumber(),
                'amount' => $subscription->price,
                'status' => 'paid',
                'billing_period_start' => $period['start'],
                'billing_period_end' => $period['end'],
                'due_date' => $period['due_date'],
                'paid_at' => $period['end'],
                'subtotal' => $subscription->price,
                'tax_rate' => config('billing.tax_rate', 0),
                'tax_amount' => $subscription->price * (config('billing.tax_rate', 0) / 100),
                'total' => $subscription->price * (1 + config('billing.tax_rate', 0) / 100),
            ]);
            
            Log::debug('Created invoice', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'period' => $period,
                'amount' => $invoice->amount
            ]);
        }
    }

    public function generate(Tenant $tenant, Invoice $invoice)
    {
        // Verify tenant-invoice relationship
        if ($invoice->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        $this->authorize('generate', $invoice);

        try {
            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'tenant' => $invoice->tenant,
                'subscription' => $invoice->subscription,
                'plan' => $invoice->subscription->plan
            ]);

            $filename = "invoices/{$invoice->number}.pdf";
            Storage::put($filename, $pdf->output());

            $invoice->update([
                'file_path' => $filename,
                'generated_at' => now(),
                'error' => false
            ]);

            return response()->json([
                'success' => true,
                'invoice' => $invoice->fresh()->load(['subscription.plan', 'tenant'])
            ]);

        } catch (\Exception $e) {
            Log::error("Invoice generation failed: {$e->getMessage()}");
            $invoice->update(['error' => true]);
            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generatePdf(Tenant $tenant, Invoice $invoice)
    {
        // Validate tenant-invoice relationship
        if ($invoice->tenant_id !== $tenant->id) {
            abort(404, 'Invoice does not belong to this tenant');
        }

        try {
            $pdfPath = $this->invoiceService->generatePdf($invoice);
            
            return response()->file(
                storage_path('app/'.$pdfPath),
                ['Content-Type' => 'application/pdf']
            );
            
        } catch (\Exception $e) {
            Log::error("PDF Generation Failed", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'PDF generation failed: '.$e->getMessage()
            ], 500);
        }
    }

    public function send(Tenant $tenant, Invoice $invoice)
    {
        // Validate relationship
        if ($invoice->tenant_id !== $tenant->id) {
            abort(404, 'Invoice does not belong to this tenant');
        }

        try {
            // Load required relationships
            $invoice->load(['tenant.admin', 'subscription.plan']);
            
            // Send email logic here
            // ...

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent to tenant admin'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Invoice send failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to send invoice: ' . $e->getMessage()
            ], 500);
        }
    }
} 