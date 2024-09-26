<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Events\NotificationDeleted;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;

class CanDeleteNotificationTest extends TestCase
{
    public function testNotificationIsDeleted()
    {
        Notification::fake();
        Event::fake();

        $this->app->config->set('snooze.deleteWhenMissingModels', true);

        $target = User::find(1);
        $notification = $target->notifyAt(new TestNotification(User::find(1)), Carbon::now()->subSeconds(10));
        $target->delete();

        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        $this->assertDatabaseMissing('scheduled_notifications', ['id' => $notification->getId()]);

        Notification::assertNothingSent();
        Event::assertDispatched(NotificationDeleted::class, 1);
    }

    public function testNotificationIsNotDeleted()
    {
        Notification::fake();
        Event::fake();

        $this->app->config->set('snooze.deleteWhenMissingModels', false);

        $target = User::find(2);

        $notification = $target->notifyAt(new TestNotification(User::find(2)), Carbon::now()->subSeconds(10));
        $target->delete();

        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $this->artisan('snooze:send');

        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);
        $this->assertFalse($notification->isSent());

        Notification::assertNothingSent();
        Event::assertNotDispatched(NotificationDeleted::class);
    }
}
