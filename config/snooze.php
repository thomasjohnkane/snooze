<?php

use Thomasjohnkane\Snooze\Models\ScheduledNotification;

return [
    /*
     * The table the scheduled notifications are stored in
     */
    'table' => 'scheduled_notifications',

    /*
     * The ScheduledNotification model to use.
     * If you need to customise the model you can override this
     */
    'model' => ScheduledNotification::class,

    /*
     * The frequency at which to send notifications
     *
     * Available options are everyMinute, everyFiveMinutes, everyTenMinutes,
     * everyFifteenMinutes, everyThirtyMinutes, hourly, and daily.
     */
    'sendFrequency' => env('SCHEDULED_NOTIFICATION_SEND_FREQUENCY', 'everyMinute'),

    /*
     * The tolerance at which to look for old notifications waiting to be sent, in seconds.
     * This is to prevent sending a large amount of notifications if the command stops
     * running. By default it's set to 24 hours
     */
    'sendTolerance' => env('SCHEDULED_NOTIFICATION_SEND_TOLERANCE', 60 * 60 * 24),

    /*
     * The age at which to prune sent/cancelled notifications, in days.
     * If set to null, pruning will be turned off. By default it's turned off
     */
    'pruneAge' => env('SCHEDULED_NOTIFICATION_PRUNE_AGE', null),
];
