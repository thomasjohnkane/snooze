<?php

namespace Thomasjohnkane\Snooze\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class PruneScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snooze:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune scheduled notifications that have been sent or cancelled';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $pruneDays = config('snooze.pruneAge');

        if ($pruneDays === null) {
            $this->error('Pruning of scheduled notifications is disabled');

            return;
        }

        $pruneBeforeDate = Carbon::now()->subDays($pruneDays);

        $notifications = ScheduledNotification::where(function ($query) {
            $query->where('sent_at', '!=', null);
            $query->orWhere('cancelled_at', '!=', null);
        })->where(function ($query) use ($pruneBeforeDate) {
            $query->where('sent_at', '<=', $pruneBeforeDate);
            $query->orWhere('cancelled_at', '<=', $pruneBeforeDate);
        });

        $totalDeleted = 0;

        do {
            $deleted = $notifications->take(1000)->delete();
            $totalDeleted += $deleted;
        } while ($deleted !== 0);

        $this->info(sprintf('Pruned %d scheduled notifications', $totalDeleted));
    }
}
