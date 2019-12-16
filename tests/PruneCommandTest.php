<?php

namespace Thomasjohnkane\Snooze\Tests;

use Carbon\Carbon;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;

class PruneCommandTest extends TestCase
{
    public function testItCanBeDisabled()
    {
        $this->artisan('snooze:prune')
            ->expectsOutput('Pruning of scheduled notifications is disabled');
    }

    public function testItDoesNotPruneRecentNotifications()
    {
        $this->app->config->set('snooze.pruneAge', 30);

        $target = User::find(1);

        $target->notifyAt(new TestNotification(User::find(2)), Carbon::now());
        $target->notifyAt(new TestNotification(User::find(2)), Carbon::now());
        $target->notifyAt(new TestNotification(User::find(2)), Carbon::now());

        $this->artisan('snooze:prune')
            ->expectsOutput('Pruned 0 scheduled notifications')
            ->assertExitCode(0);
    }

    public function testPrunesCorrectNotifications()
    {
        $this->app->config->set('snooze.pruneAge', 30);

        $target = User::find(1);

        $notification = $target->notifyAt(new TestNotification(User::find(2)), Carbon::now());
        $base = ScheduledNotificationModel::find($notification->getId());

        $sent2MonthsAgo = $base->replicate();
        $sent2MonthsAgo->sent_at = Carbon::now()->subMonths(2);
        $sent2MonthsAgo->save();

        $cancelled2MonthsAgo = $base->replicate();
        $cancelled2MonthsAgo->cancelled_at = Carbon::now()->subMonths(2);
        $cancelled2MonthsAgo->save();

        $sent1WeekAgo = $base->replicate();
        $sent1WeekAgo->sent_at = Carbon::now()->subWeek();
        $sent1WeekAgo->save();

        $cancelled1WeekAgo = $base->replicate();
        $cancelled1WeekAgo->cancelled_at = Carbon::now()->subWeek();
        $cancelled1WeekAgo->save();

        $this->artisan('snooze:prune')
            ->expectsOutput('Pruned 2 scheduled notifications')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('scheduled_notifications', ['id' => $sent2MonthsAgo->id]);
        $this->assertDatabaseMissing('scheduled_notifications', ['id' => $cancelled2MonthsAgo->id]);

        $this->assertDatabaseHas('scheduled_notifications', ['id' => $base->id]);
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $sent1WeekAgo->id]);
        $this->assertDatabaseHas('scheduled_notifications', ['id' => $cancelled1WeekAgo->id]);
    }
}
