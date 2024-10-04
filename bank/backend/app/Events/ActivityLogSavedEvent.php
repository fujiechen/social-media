<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\Activitylog\Models\Activity;

class ActivityLogSavedEvent
{
    use SerializesModels;

    public Activity $activityLog;

    public function __construct(Activity $activityLog) {
        $this->activityLog = $activityLog;
    }
}
