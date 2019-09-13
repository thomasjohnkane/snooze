<?php

namespace Thomasjohnkane\Snooze\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snooze:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications that are ready to be sent.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $notifications = ScheduledNotification::whereSent(false)
                                ->whereCancelled(false)
                                ->where('send_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                                ->where('send_at', '>=', Carbon::now()->subDay()->format('Y-m-d H:i:s'))
                                ->get();

        if (! $notifications->count()) {
            $this->info('No Scheduled Notifications need to be sent.');

            return;
        }

        $this->info('Starting Sending Scheduled Notifications');

        $bar = $this->output->createProgressBar(count($notifications));

        $bar->start();

        $this->info(sprintf('Sending %d scheduled notifications...', $notifications->count()));

        $notifications->each(function (ScheduledNotification $notification) use ($bar) {
            $bar->advance();

            try {
                $notification->send();
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                Log::error($e->getMessage());
            }
        });

        $bar->finish();

        $this->info('Finished Sending Scheduled Notifications');
    }
}
