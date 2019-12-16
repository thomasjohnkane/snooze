<?php

namespace Thomasjohnkane\Snooze;

use Illuminate\Console\Scheduling\Schedule;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__.'/../config/snooze.php';

    protected $commands = [
        Console\Commands\SendScheduledNotifications::class,
        Console\Commands\PruneScheduledNotifications::class,
    ];

    public function boot()
    {
        // Schedule base command to run every minute
        $this->app->booted(function () {
            $frequency = config('snooze.sendFrequency', 'everyMinute');
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('snooze:send')->{$frequency}();

            if (config('snooze.pruneAge') !== null) {
                $schedule->command('snooze:prune')->daily();
            }
        });

        $this->publishes([
            self::CONFIG_PATH => config_path('snooze.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'snooze'
        );

        $this->app->bind('snooze', function () {
            return new Snooze();
        });
    }
}
