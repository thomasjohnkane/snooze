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
];
