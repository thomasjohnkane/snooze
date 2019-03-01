<?php

namespace Thomasjohnkane\SimpleScheduledNotifications;

use Illuminate\Console\Scheduling\Schedule;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/simple-scheduled-notifications.php';

    protected $commands = [
        Console\Commands\SendScheduledNotifications::class,
        Console\Commands\NotificationMakeCommand::class,
        Console\Commands\MailMakeCommand::class,
    ];

    public function boot()
    {
        // Schedule base command to run every minute
        
        $this->app->booted(function () {
            $frequency = config('simple-scheduled-notifications.send_frequency');
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('ssn:send')->{$frequency}();
        });

        $this->publishes([
            self::CONFIG_PATH => config_path('simple-scheduled-notifications.php'),
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
            'simple-scheduled-notifications'
        );

        $this->app->bind('simple-scheduled-notifications', function () {
            return new SimpleScheduledNotifications();
        });
    }
}
