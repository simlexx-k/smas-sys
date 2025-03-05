<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestSubscriptionSeeder extends Seeder
{
    public function run()
    {
        // Set a default admin user for activity logging
        $adminUser = User::where('email', 'simonwriter254@gmail.com')->first();
        if (!$adminUser) {
            throw new \Exception('Admin user not found. Please ensure the admin user exists.');
        }
        Auth::login($adminUser);

        try {
            // Create a past subscription for tenant 7
            $pastSubscription = Subscription::create([
                'tenant_id' => 7,
                'plan_id' => Plan::where('slug', 'basic-plan')->first()->id,
                'status' => 'expired',
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->subMonth(),
                'price' => '29.99',
                'features' => json_encode([
                    'Up to 100 students',
                    'Basic attendance tracking',
                    'Report card generation',
                    'Email support'
                ]),
                'payment_method' => 'bank_transfer'
            ]);

            // Create a test invoice for the past subscription
            Invoice::create([
                'subscription_id' => $pastSubscription->id,
                'tenant_id' => 7,
                'number' => Invoice::generateNumber(),
                'amount' => 29.99,
                'status' => 'paid',
                'billing_period_start' => $pastSubscription->starts_at,
                'billing_period_end' => $pastSubscription->ends_at,
                'due_date' => now()->subMonths(2)->addDays(30),
                'paid_at' => now()->subMonths(2),
                'payment_method' => 'bank_transfer',
                'subtotal' => 29.99,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => 29.99
            ]);

            // Log success
            \Log::info('Test subscription and invoice created successfully', [
                'subscription_id' => $pastSubscription->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create test subscription', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 