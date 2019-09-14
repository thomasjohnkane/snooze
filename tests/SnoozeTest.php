<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Thomasjohnkane\Snooze\Facades\Snooze;
use Thomasjohnkane\Snooze\ServiceProvider;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class SnoozeTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'snooze' => Snooze::class,
        ];
    }

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
            'user_id',
            'type',
            'data',
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
    public function testItCreatesNotification()
    {
        $notification_data = [
            'user_id' => 1,
            'type'    => 'App/Notifications/TestNotification.php',
            'send_at' => '2019-10-10 00:00:00',
            'data'    => [
                'test_id' => 1,
            ],
        ];

        $notification = ScheduledNotification::create($notification_data);
        $notification = $notification->fresh();

        $this->assertInstanceOf(ScheduledNotification::class, $notification);
        $this->assertSame($notification_data['user_id'], $notification->user_id);
        $this->assertSame($notification_data['type'], $notification->type);
        $this->assertInstanceOf(Carbon::class, $notification->send_at);
        $this->assertSame($notification_data['send_at'], $notification->send_at->format('Y-m-d H:i:s'));
        $this->assertSame($notification_data['data']['test_id'], $notification->data['test_id']);
        $this->assertFalse($notification->cancelled);
        $this->assertFalse($notification->sent);
        $this->assertFalse($notification->rescheduled);
    }

    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testItInsertsManyNotifications()
    {
        $now = Carbon::now();

        $notifications = ScheduledNotification::insert([
            [
                'user_id'    => 1,
                'send_at'    => $now->copy()->addHour()->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\SignUpConfirmation',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id'    => 2,
                'send_at'    => $now->copy()->addDays(3)->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\WelcomeToCommunity',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id'    => 3,
                'send_at'    => $now->copy()->addWeek()->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\UpsellSubscription',
                'created_at' => $now,
                'updated_at' => $now,

            ],
        ]);

        $this->assertEquals($notifications, 3);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 1,
        ]);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 2,
        ]);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 3,
        ]);
    }

    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testCancelsNotification()
    {
        $notification = ScheduledNotification::whereCancelled(false)->first();

        $this->assertEquals($notification->cancelled, false);

        $notification->cancel();

        $this->assertEquals($notification->cancelled, true);
    }

    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testReschedulesNotification()
    {
        $notification = ScheduledNotification::whereCancelled(false)->first();
        $originalSendAt = $notification->send_at;
        $notification->reschedule($originalSendAt->copy()->addDays(3));

        $this->assertNotEquals($notification->send_at, $originalSendAt);
        $this->assertEquals($notification->send_at, $originalSendAt->copy()->addDays(3));
    }

    /**
     * Check that the multiply method returns correct result.
     * @return void
     */
    public function testDuplicatesNotification()
    {
        $notification = ScheduledNotification::whereCancelled(true)->first();
        $originalSendAt = $notification->send_at;
        $duplicate = $notification->scheduleAgainAt($originalSendAt->copy()->addDays(7));

        $this->assertInstanceOf(ScheduledNotification::class, $duplicate);
        $this->assertNotEquals($duplicate->id, $notification->id);
        $this->assertNotEquals($duplicate->send_at, $notification->send_at);
        $this->assertEquals($duplicate->send_at, $originalSendAt->copy()->addDays(7));
    }
}
