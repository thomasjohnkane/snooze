<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Events\NotificationInterrupted;
use Thomasjohnkane\Snooze\Events\NotificationRescheduled;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestInterruptableNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestReschedulableNotification;

class CanRescheduleNotificationTest extends TestCase
{
    public function testNotificationIsRescheduled()
    {
        Notification::fake();
        Event::fake();

        // User id 1 should be interrupted
        $target = User::find(1);
        $notification = $target->notifyAt(new TestReschedulableNotification(User::find(2)), Carbon::now()->subSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        $notification->refresh();
        $this->assertFalse($notification->isSent());
        $this->assertFalse($notification->isCancelled());
        $this->assertNotNull($notification->shouldRescheduleFor());

        Notification::assertNothingSent();
        Event::assertDispatched(NotificationRescheduled::class, 1);
    }

    public function testNotificationIsNotRescheduled()
    {
        Notification::fake();
        Event::fake();

        // User id 2 should NOT be rescheduled.
        $target = User::find(2);

        $notification = $target->notifyAt(new TestReschedulableNotification(User::find(2)), Carbon::now()->subSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);
        $this->artisan('snooze:send');

        $notification->refresh();
        $this->assertTrue($notification->isSent());
        $this->assertFalse($notification->isCancelled());
        $this->assertNull($notification->shouldRescheduleFor());
    }

    public function testInterruptMethodReceivesNotifiable()
    {
        $target = User::find(3);

        $notificationMock = $this->createMock(TestReschedulableNotification::class);
        $notificationMock
            ->expects($this->once())
            ->method('shouldRescheduleFor')
            ->with($target)
            ->willReturn(null);

        $model = new ScheduledNotification();
        $model->shouldRescheduleFor($notificationMock, $target);
    }
}
