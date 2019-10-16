<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Events\NotificationInterrupted;
use Thomasjohnkane\Snooze\Tests\Notifications\TestInterruptableNotification;

class CanInterruptNotificationTest extends TestCase
{
    public function testNotificationIsInterrupted()
    {
        // Arrange
        // Create Stub
        Notification::fake();

        Event::fake();

        $target = User::find(1);

        // Act
        $notification = $target->notifyAt(new TestInterruptableNotification(User::find(2)), Carbon::now()->addSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        // Assert
        // Check wasn't sent (exception?)
        $this->assertFalse($notification->isSent());
        $this->assertTrue($notification->getShouldInterrupt());

        Notification::assertNothingSent();

        Event::assertDispatched(NotificationInterrupted::class, 1);
    }
}
