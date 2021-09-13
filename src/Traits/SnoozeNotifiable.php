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
     * @param  Notification  $notification
     * @param  DateTimeInterface  $sendAt
     * @param  array  $meta
     * @return ScheduledNotification
     *
     * @throws SchedulingFailedException
     */
    public function notifyAt($notification, DateTimeInterface $sendAt, array $meta = []): ScheduledNotification
    {
        return ScheduledNotification::create($this, $notification, $sendAt, $meta);
    }
}
