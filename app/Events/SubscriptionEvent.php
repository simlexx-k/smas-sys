<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class SubscriptionEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {}
}

class SubscriptionCreated extends SubscriptionEvent {}
class SubscriptionRenewed extends SubscriptionEvent {}
class SubscriptionCancelled extends SubscriptionEvent {}
class SubscriptionExpiring extends SubscriptionEvent {}
class PaymentFailed extends SubscriptionEvent {} 