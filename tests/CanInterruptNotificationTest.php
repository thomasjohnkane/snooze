<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestInterruptableNotification;

class CanInterruptNotificationTest extends TestCase
{
    public function testNotificationIsInterrupted()
    {
        // Arrange
        // Create Stub
        Notification::fake();

        $target = User::find(1);

        // Act
        $notification = $target->notifyAt(new TestInterruptableNotification(User::find(2)), Carbon::now()->addSeconds(10));
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        // Assert
        // Check wasn't sent (exception?)
        $this->assertFalse($notification->isSent());
        $this->assertTrue($notification->shouldInterrupt());

        Notification::assertNothingSent();
    }
}
