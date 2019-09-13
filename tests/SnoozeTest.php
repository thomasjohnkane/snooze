<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;

class SnoozeTest extends TestCase
{
    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testItRunsMigrations()
    {
        $this->artisan('migrate')->run();

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

    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testItCreatesAndSendsNotification()
    {
        Notification::fake();

        $target = User::find(1);

        $notification = ScheduledNotification::schedule(
            $target,
            new TestNotification(User::find(2)),
            Carbon::now()->addSeconds(10)
        );

        $notification->send();

        $this->assertTrue($notification->sent);

        Notification::assertSentTo(
            $target,
            TestNotification::class,
            function ($notification) {
                return $notification->newUser->id === 2;
            }
        );
    }
}
