<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Events\NotificationInterrupted;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestInterruptableNotification;

class CanInterruptNotificationTest extends TestCase
{
    public function testNotificationIsInterrupted()
    {
        Notification::fake();
        Event::fake();

        // User id 1 should be interrupted
        $target = User::find(1);
        $notification = $target->notifyAt(new TestInterruptableNotification(User::find(2)), Carbon::now()->subSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        $notification->refresh();
        $this->assertFalse($notification->isSent());
        $this->assertTrue($notification->isCancelled());
        $this->assertTrue($notification->shouldInterrupt());

        Notification::assertNothingSent();
        Event::assertDispatched(NotificationInterrupted::class, 1);
    }

    public function testNotificationIsNotInterrupted()
    {
        Notification::fake();
        Event::fake();

        // User id 2 should NOT be interrupted
        $target = User::find(2);

        $notification = $target->notifyAt(new TestInterruptableNotification(User::find(2)), Carbon::now()->subSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);
        $this->artisan('snooze:send');

        $notification->refresh();
        $this->assertTrue($notification->isSent());
        $this->assertFalse($notification->isCancelled());
        $this->assertFalse($notification->shouldInterrupt());
    }

    public function testInterruptMethodReceivesNotifiable()
    {
        $target = User::find(3);

        $notificationMock = $this->createMock(TestInterruptableNotification::class);
        $notificationMock
            ->expects($this->once())
            ->method('shouldInterrupt')
            ->with($target)
            ->willReturn(true);

        $model = new ScheduledNotification();
        $model->shouldInterrupt($notificationMock, $target);
    }
}
