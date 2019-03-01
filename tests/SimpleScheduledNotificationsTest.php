<?php

namespace Thomasjohnkane\SimpleScheduledNotifications\Tests;

use Thomasjohnkane\SimpleScheduledNotifications\Facades\SimpleScheduledNotifications;
use Thomasjohnkane\SimpleScheduledNotifications\ServiceProvider;
use Thomasjohnkane\SimpleScheduledNotifications\Models\SsNotification;
use Orchestra\Testbench\TestCase;
use Carbon\Carbon;

class SimpleScheduledNotificationsTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'simple-scheduled-notifications' => SimpleScheduledNotifications::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * Check that the multiply method returns correct result
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
     * Check that the multiply method returns correct result
     * @return void
     */
    public function testItCreatesNotification()
    {
        $notification_data = [
            'user_id' => 1,
            'type'    => 'App/Notifications/TestNotification.php',
            'send_at' => '2019-10-10 00:00:00',
            'data'    => [
                'test_id' => 1
            ],
        ];
      
        $notification = SsNotification::create($notification_data);
        $notification = $notification->fresh();

        $this->assertInstanceOf(SsNotification::class, $notification);
        $this->assertEquals($notification_data['user_id'], $notification->user_id);
        $this->assertEquals($notification_data['type'], $notification->type);
        $this->assertEquals($notification_data['send_at'], $notification->send_at);
        $this->assertEquals($notification_data['data']['test_id'], $notification->data['test_id']);
        $this->assertEquals(0, $notification->cancelled);
        $this->assertEquals(0, $notification->sent);
        $this->assertEquals(0, $notification->rescheduled);
    }

    /**
     * Check that the multiply method returns correct result
     * @return void
     */
    public function testItInsertsManyNotifications()
    {
        $now = Carbon::now();

        $notifications = SsNotification::insert([
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
                'updated_at' => $now
            ],
            [
                'user_id'    => 3,
                'send_at'    => $now->copy()->addWeek()->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\UpsellSubscription',
                'created_at' => $now,
                'updated_at' => $now

            ],
        ]);

        $this->assertEquals($notifications, 3);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 1
        ]);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 2
        ]);

        $this->assertDatabaseHas('scheduled_notifications', [
            'user_id' => 3
        ]);
    }

    /**
     * Check that the multiply method returns correct result
     * @return void
     */
    public function testCancelsNotification()
    {

        $notification = SsNotification::whereCancelled(0)->first();

        $this->assertEquals($notification->cancelled(), FALSE);

        $notification->cancel();

        $this->assertEquals($notification->cancelled(), TRUE);

    }

    /**
     * Check that the multiply method returns correct result
     * @return void
     */
    public function testReschedulesNotification()
    {

        $notification = SsNotification::whereCancelled(0)->first();
        $original_send_at = Carbon::createFromFormat('Y-m-d H:i:s', $notification->send_at);
        $notification->reschedule($original_send_at->copy()->addDays(3));

        $this->assertNotEquals($notification->send_at, $original_send_at);
        $this->assertEquals($notification->send_at, $original_send_at->copy()->addDays(3));

    }

    /**
     * Check that the multiply method returns correct result
     * @return void
     */
    public function testDuplicatesNotification()
    {

        $notification = SsNotification::whereCancelled(1)->first();
        $original_send_at = Carbon::createFromFormat('Y-m-d H:i:s', $notification->send_at);
        $duplicate = $notification->scheduleAgainAt($original_send_at->copy()->addDays(7));

        $this->assertInstanceOf(SsNotification::class, $duplicate);
        $this->assertNotEquals($duplicate->id, $notification->id);
        $this->assertNotEquals($duplicate->send_at, $notification->send_at);
        $this->assertEquals($duplicate->send_at, $original_send_at->copy()->addDays(7));

    }
}
