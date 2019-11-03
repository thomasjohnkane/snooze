<?php

declare(strict_types=1);

namespace Thomasjohnkane\Snooze\Traits;

use DateTimeInterface;
use Illuminate\Notifications\Notification;
use Thomasjohnkane\Snooze\Exception\SchedulingFailedException;
use Thomasjohnkane\Snooze\ScheduledNotification;

trait SnoozeNotifiable
{
    /**
     * @param Notification      $notification
     * @param DateTimeInterface $sendAt
     *
     * @return ScheduledNotification
     * @throws SchedulingFailedException
     */
    public function notifyAt($notification, DateTimeInterface $sendAt): ScheduledNotification
    {
        return ScheduledNotification::create($this, $notification, $sendAt);
    }
}
