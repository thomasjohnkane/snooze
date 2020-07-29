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
            'target_id',
            'target_type',
            'target',
            'notification_type',
            'notification',
            'send_at',
            'sent_at',
            'rescheduled_at',
            'cancelled_at',
            'created_at',
            'updated_at',
        ], $columns);
    }

    public function testItCreatesAndSendsNotification()
    {
        Notification::fake();
        Carbon::setTestNow('2025-01-01 01:00:00');

        $target = User::find(1);

        /** @var ScheduledNotification $notification */
        $notification = $target->notifyAt(new TestNotification(User::find(2)), Carbon::now()->addSeconds(10));

        $this->assertInstanceOf(ScheduledNotification::class, $notification);
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $notification->getId()]);

        $notification->sendNow();

        $this->assertTrue($notification->isSent());
        $this->assertFalse($notification->isRescheduled());
        $this->assertFalse($notification->isCancelled());
        $this->assertSame(TestNotification::class, $notification->getType());

        $this->assertEquals(Carbon::now(), $notification->getSentAt());
        $this->assertNull($notification->getCancelledAt());
        $this->assertNull($notification->getRescheduledAt());

        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getSendAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getUpdatedAt());

        $this->assertEquals(1, $notification->getTargetId());
        $this->assertSame(User::class, $notification->getTargetType());

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

    public function testNotificationCanBeRescheduled()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification2 = $notification->reschedule(Carbon::parse('2040-01-01'));
        $this->assertSame('2040-01-01', $notification2->getSendAt()->format('Y-m-d'));

        $notification3 = $notification->reschedule('2050-01-01');
        $this->assertSame('2050-01-01', $notification3->getSendAt()->format('Y-m-d'));

        $notification3->sendNow();

        // Force reschedule
        $notification4 = $notification->reschedule('2060-01-01', true);
        $this->assertSame('2060-01-01', $notification4->getSendAt()->format('Y-m-d'));
        $this->assertNotSame($notification3->getId(), $notification4->getId());
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
        $notification3 = $notification->scheduleAgainAt('2050-01-01');

        $this->assertNotSame($notification->getId(), $notification2->getId());
        $this->assertNotSame($notification->getId(), $notification3->getId());
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

        $notification->reschedule(Carbon::now()->addDay());
    }

    public function testCancelledNotificationCannotBeRescheduled()
    {
        $target = User::find(1);

        $notification = ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->cancel();

        $this->expectException(NotificationCancelledException::class);

        $notification->reschedule(Carbon::now()->addDay());
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

    public function testCannotCreateNotificationWithPastSentAt()
    {
        $this->expectException(SchedulingFailedException::class);
        $target = User::find(1);

        ScheduledNotification::create(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->subHour()
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

        $this->assertSame(5, ScheduledNotification::findByTarget($target)->count());

        $all->first()->sendNow();

        $allNotSent = ScheduledNotification::all();
        $this->assertSame(4, $allNotSent->count());

        $all = ScheduledNotification::all(true);
        $this->assertSame(5, $all->count());
    }

    public function testNotificationClassCanBeRetreived()
    {
        $target = User::find(1);
        $notification = new TestNotification(User::find(2));

        $scheduled_notification = ScheduledNotification::create($target, $notification, Carbon::now()->addSeconds(10));

        $this->assertInstanceOf(TestNotification::class, $scheduled_notification->getNotification());
        $this->assertEquals($scheduled_notification->getNotification()->newUser->email, $notification->newUser->email);
    }
}
