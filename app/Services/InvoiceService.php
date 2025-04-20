<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generatePdf(Invoice $invoice): string
    {
        try {
            // Load relationships
            $invoice->load(['subscription.plan', 'tenant']);

            $data = [
                'invoice' => $invoice->toArray(),
                'tenant' => $invoice->tenant->toArray(),
                'subscription' => $invoice->subscription->toArray(),
                'plan' => $invoice->subscription->plan->toArray()
            ];
            
            // Add validation
            throw_unless(
                is_string($data['plan']['name']), 
                \Exception::class, 
                'Invalid plan name type'
            );

            $filename = 'invoice-'.$invoice->id.'-'.Str::random(8).'.pdf';
            $storagePath = $filename;

            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'tenant' => $invoice->tenant,
                'subscription' => $invoice->subscription,
                'plan' => $invoice->subscription->plan,
                'total' => $invoice->total
            ]);

            Storage::disk('invoices')->put($filename, $pdf->output());

            return $storagePath;

        } catch (\Exception $e) {
            throw new \Exception("PDF generation failed: ".$e->getMessage());
        }
    }
} 