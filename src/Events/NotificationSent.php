<?php

namespace Thomasjohnkane\Snooze\Events;

use Illuminate\Queue\SerializesModels;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class NotificationSent
{
    use SerializesModels;

    public $scheduledNotification;

    /**
     * Create a new event instance.
     *
     * @param  \Thomasjohnkane\Snooze\Models\ScheduledNotification  $scheduledNotification
     * @return void
     */
    public function __construct(ScheduledNotification $scheduledNotification)
    {
        $this->scheduledNotification = $scheduledNotification;
    }
}
