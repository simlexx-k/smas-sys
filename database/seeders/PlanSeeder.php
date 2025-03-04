<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'slug' => 'basic-plan',
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
            ],
            // Add more plans here...
        ];

        foreach ($plans as $planData) {
            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            Log::info('Created/Updated plan', [
                'plan_id' => $plan->id,
                'plan_data' => $plan->toArray()
            ]);
        }
    }
}