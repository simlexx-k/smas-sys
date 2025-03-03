<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'name' => 'Basic Plan',
            'description' => 'Perfect for small schools',
            'price' => 29.99,
            'billing_period' => 'monthly',
            'trial_period_days' => 14,
            'features' => [
                'Up to 100 students',
                'Basic attendance tracking',
                'Report card generation',
                'Email support'
            ],
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Add more plans...
    }
}