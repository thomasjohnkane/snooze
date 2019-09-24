<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Exception\NotificationAlreadySentException;
use Thomasjohnkane\Snooze\Exception\NotificationCancelledException;
use Thomasjohnkane\Snooze\Exception\SchedulingFailedException;
use Thomasjohnkane\Snooze\ScheduledNotification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotificationTwo;

class SendCommandTest extends TestCase
{

    public function testItSendScheduledNotifications()
    {
        Notification::fake();
        $target = User::find(1);

        // Should be sent
        $notification1 = $target->notifyAt(new TestNotification(User::find(2)), Carbon::now());
        // Should not be sent, cancelled below
        $notification2 = $target->notifyAt(new TestNotification($target), Carbon::now());
        // Should not be sent
        $notification3 = $target->notifyAt(new TestNotification($target), Carbon::now()->addDay());

        $notification2->cancel();

        $this->artisan('snooze:send')
            ->expectsOutput('Starting Sending Scheduled Notifications')
            ->assertExitCode(0);

        $notification1 =ScheduledNotification::find($notification1->getId());
        $this->assertTrue($notification1->isSent());

        $notification2 = ScheduledNotification::find($notification2->getId());
        $this->assertFalse($notification2->isSent());
        $this->assertTrue($notification2->isCancelled());

        $notification3 = ScheduledNotification::find($notification3->getId());
        $this->assertFalse($notification3->isSent());

        Notification::assertSentTo(
            $target,
            TestNotification::class,
            function ($notification) {
                return $notification->newUser->id === 2;
            }
        );
    }

    public function testNoScheduledNotifications()
    {
        $this->artisan('snooze:send')
            ->expectsOutput('No Scheduled Notifications need to be sent.')
            ->assertExitCode(0);
    }
}
