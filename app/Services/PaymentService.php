<?php

namespace App\Services;

use App\Models\Subscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Subscription $subscription): array
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => $subscription->price * 100, // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                ],
            ]);

            return [
                'clientSecret' => $intent->client_secret,
                'success' => true,
            ];
        } catch (ApiErrorException $e) {
            report($e);
            return [
                'error' => $e->getMessage(),
                'success' => false,
            ];
        }
    }

    public function processPayment(string $paymentIntentId): bool
    {
        try {
            $intent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            
            if ($intent->status === 'succeeded') {
                $subscription = Subscription::find($intent->metadata['subscription_id']);
                $subscription->update([
                    'last_payment_at' => now(),
                    'payment_method' => 'stripe',
                    'status' => Subscription::STATUS_ACTIVE,
                ]);
                
                return true;
            }
            
            return false;
        } catch (ApiErrorException $e) {
            report($e);
            return false;
        }
    }
} 