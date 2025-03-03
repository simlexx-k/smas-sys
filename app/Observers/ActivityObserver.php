<?php

namespace App\Observers;

use App\Models\Activity;
use App\Events\ActivityLogged;

class ActivityObserver
{
    public function created(Activity $activity): void
    {
        broadcast(new ActivityLogged($activity))->toOthers();
    }
} 