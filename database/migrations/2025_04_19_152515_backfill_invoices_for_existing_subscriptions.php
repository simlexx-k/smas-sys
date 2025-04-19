<?php

use App\Models\Subscription;
use App\Http\Controllers\Admin\InvoiceController;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Subscription::with(['plan', 'tenant'])->chunk(100, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $controller = new InvoiceController();
                
                // Calculate billing periods
                $start = $subscription->starts_at;
                $end = $subscription->ends_at;
                
                // Only create invoices for completed periods
                if ($end->isPast()) {
                    $controller->generateForSubscription($subscription);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Add rollback logic if needed
    }
};
