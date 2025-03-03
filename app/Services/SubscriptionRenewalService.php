<?php

namespace App\Services;

use App\Models\Subscription;
use App\Events\SubscriptionExpiring;
use App\Events\PaymentFailed;
use App\Events\SubscriptionRenewed;
use Carbon\Carbon;

class SubscriptionRenewalService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function processRenewals(): void
    {
        // Process subscriptions expiring in the next 3 days
        $expiringSubscriptions = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '<=', now()->addDays(3))
            ->where('ends_at', '>', now())
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            event(new SubscriptionExpiring($subscription));
        }

        // Process auto-renewals
        $dueForRenewal = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('auto_renew', true)
            ->where('ends_at', '<=', now()->addDay())
            ->get();

        foreach ($dueForRenewal as $subscription) {
            $this->renewSubscription($subscription);
        }
    }

    private function renewSubscription(Subscription $subscription): void
    {
        try {
            // Create payment intent
            $result = $this->paymentService->createPaymentIntent($subscription);
            
            if (!$result['success']) {
                event(new PaymentFailed($subscription));
                return;
            }

            // Process payment
            if ($this->paymentService->processPayment($result['clientSecret'])) {
                $subscription->update([
                    'ends_at' => Carbon::parse($subscription->ends_at)->addMonth(),
                    'last_payment_at' => now(),
                    'next_payment_at' => Carbon::parse($subscription->ends_at)->addMonth(),
                ]);

                event(new SubscriptionRenewed($subscription));
            } else {
                event(new PaymentFailed($subscription));
            }
        } catch (\Exception $e) {
            report($e);
            event(new PaymentFailed($subscription));
        }
    }
} 