<?php

namespace Thomasjohnkane\Snooze\Events;

use Illuminate\Queue\SerializesModels;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class NotificationInterrupted
{
    use SerializesModels;

    public $scheduledNotification;

    /**
     * Create a new event instance.
     *
     * @param  \App\Order  $order
     * @return void
     */
    public function __construct(ScheduledNotification $scheduledNotification)
    {
        $this->scheduledNotification = $scheduledNotification;
    }
}
