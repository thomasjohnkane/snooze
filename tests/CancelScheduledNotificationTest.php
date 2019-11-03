<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Exception\LaravelSnoozeException;
use Thomasjohnkane\Snooze\ScheduledNotification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotificationTwo;

class CancelScheduledNotificationTest extends TestCase
{
    public function testModelNotificationsCanBeCanceled()
    {
        Notification::fake();

        $target1 = User::find(1);
        $target2 = User::find(3);

        ScheduledNotification::create(
            $target1,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );
        ScheduledNotification::create(
            $target1,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(11)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        $all = ScheduledNotification::all();
        $this->assertSame(5, $all->count());

        ScheduledNotification::cancelByTarget($target2);

        $all = ScheduledNotification::all();
        $this->assertSame(2, $all->count());
        $this->assertEquals(1, $all->first()->getTargetId());
    }

    public function testAnonNotificationsCanBeCanceled()
    {
        Notification::fake();

        $target1 = (new AnonymousNotifiable())->route('email', 'hello@example.com');
        $target2 = (new AnonymousNotifiable())->route('email', 'goodbye@example.com');

        ScheduledNotification::create(
            $target1,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );
        ScheduledNotification::create(
            $target1,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(11)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target2,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        $all = ScheduledNotification::all();
        $this->assertSame(5, $all->count());

        ScheduledNotification::cancelAnonymousNotificationsByChannel('email', 'goodbye@example.com');

        $all = ScheduledNotification::all();
        $this->assertSame(2, $all->count());
        $this->assertNull($all->first()->getTargetId());
    }

    public function testCannotCancelAnonTarget()
    {
        $target = (new AnonymousNotifiable())->route('email', 'hello@example.com');

        $this->expectException(LaravelSnoozeException::class);
        $this->expectExceptionMessage('Cannot cancel AnonymousNotifiable by instance');

        $this->assertNull(ScheduledNotification::findByTarget($target));
        ScheduledNotification::cancelByTarget($target);
    }
}
