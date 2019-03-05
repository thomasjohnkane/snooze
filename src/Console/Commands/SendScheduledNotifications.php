<?php

namespace Thomasjohnkane\ScheduledNotifications\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Thomasjohnkane\ScheduledNotifications\Models\SsNotification;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssn:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications that are ready to be sent.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting "SendScheduledNotifications" Command...');

        $notifications = SsNotification::whereSent(0)
                                ->whereCancelled(0)
                                ->where('send_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                                ->where('send_at', '>=', Carbon::now()->subDay()->format('Y-m-d H:i:s'))
                                ->get();

        if (! $notifications->count()) {
            $this->info('No Scheduled Notifications need to be sent.');

            return false;
        }

        $bar = $this->output->createProgressBar(count($notifications));

        $bar->start();

        $this->info('Sending '.$notifications->count().' scheduled notifications...');

        $notifications->each(function ($item, $key) use ($bar) {
            $bar->advance();

            try {
                $user = User::find($item->user_id);
                $user->notify(new $item->type($item->data));
            } catch (Exception $e) {
                $this->error($e->getMessage());
                Log::error([$e->getMessage()]);
            }

            // Change status to sent
            $item->sent = 1;
            $item->save();
        });

        $bar->finish();

        $this->info('SendScheduledNotifications Command has finished sending.');

        return true;
    }
}
