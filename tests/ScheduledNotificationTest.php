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

class ScheduledNotificationTest extends TestCase
{
    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testItRunsMigrations()
    {

        $columns = \Schema::getColumnListing('scheduled_notifications');
        $this->assertEquals([
            'id',
            'type',
            'target',
            'notification',
            'send_at',
            'sent',
            'rescheduled',
            'cancelled',
            'created_at',
            'updated_at',
        ], $columns);
    }

    public function testItCreatesAndSendsNotification()
    {
        Notification::fake();

        $target = User::find(1);

        $notification = $target->notifyAt(new TestNotification(User::find(2)),  Carbon::now()->addSeconds(10));

        $this->assertInstanceOf(ScheduledNotification::class, $notification);
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $notification->sendNow();

        $this->assertTrue($notification->isSent());
        $this->assertFalse($notification->isRescheduled());
        $this->assertFalse($notification->isCancelled());
        $this->assertSame(TestNotification::class, $notification->getType());

        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getSendAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getUpdatedAt());

        Notification::assertSentTo(
            $target,
            TestNotification::class,
            function ($notification) {
                return $notification->newUser->id === 2;
            }
        );

        $this->assertNotNull(ScheduledNotification::find($notification->getId()));
    }

    public function testNewNotificationCanBeCancelled()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->cancel();

        $this->assertTrue($notification->isCancelled());

        $this->expectException(NotificationCancelledException::class);

        $notification->sendNow();
    }

    public function testSentNotificationCannotBeCancelled()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->sendNow();

        $this->assertTrue($notification->isSent());

        $this->expectException(NotificationAlreadySentException::class);

        $notification->cancel();
    }

    public function testSentNotificationCannotBeSentAgain()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->sendNow();

        $this->assertTrue($notification->isSent());

        $this->expectException(NotificationAlreadySentException::class);

        $notification->sendNow();
    }

    public function testSentNotificationCanBeScheduledAgain()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->sendNow();

        $this->assertTrue($notification->isSent());
        $notification2 = $notification->scheduleAgainAt(Carbon::now()->addDay());

        $this->assertNotSame($notification->getId(), $notification2->getId());
    }

    public function testSentNotificationCannotBeRescheduled()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->sendNow();

        $this->expectException(NotificationAlreadySentException::class);

        $notification2 = $notification->reschedule(Carbon::now()->addDay());

        $this->assertNotSame($notification->getId(), $notification2->getId());
    }

    public function testCannotCreateNotificationWithNonNotifiable()
    {
        $this->expectException(SchedulingFailedException::class);

        ScheduledNotification::create(
            new \StdClass(),
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );
    }

    public function testNotificationsCanBeQueried()
    {
        Notification::fake();

        $target = User::find(1);

        ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(30)
        );

        ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        ScheduledNotification::create(
            $target,
            new TestNotificationTwo(User::find(2)),
            Carbon::now()->addSeconds(60)
        );

        $all = ScheduledNotification::all();
        $this->assertSame(5, $all->count());

        $type1 = ScheduledNotification::findByType(TestNotification::class);
        $this->assertSame(3, $type1->count());

        $type2 = ScheduledNotification::findByType(TestNotificationTwo::class);
        $this->assertSame(2, $type2->count());

        $all->first()->sendNow();

        $allNotSent = ScheduledNotification::all();
        $this->assertSame(4, $allNotSent->count());

        $all = ScheduledNotification::all(true);
        $this->assertSame(5, $all->count());
    }
}
