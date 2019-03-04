[< Back to main README.md](../../README.md)
# Basic Delayed Notification Example (1 week)

1. Create the basic notification: `php artisan make:notification:scheduled OneWeekAfterNotice`
    * <small>Note: this uses a custom generator. You could do this with the normal generator though if you prefer</small>

2. Get the datetime for when we should send the notification
    * `$send_at = Carbon::now()->addDays(7)->format('Y-m-d H:i:s')` // 7 days from now
    * Here is a reference to [Carbon][1] if you need help creating future dates
    * <small>Note: Make sure you have imported carbon with `use Carbon\Carbon;` at the top of your file</small>

3. Schedule the notification for a notifiable User
```php
// use Thomasjohnkane\ScheduledNotifications\Models\SsNotification;;

$notification = SsNotification::create([
    'user_id' => Auth::id(),
    'send_at' => $send_at,
    'type' => 'App\Notifications\OneWeekAfterNotice'
]);
```

4. Our notification will be saved in our `scheduled_notifications` table, and will be sent the first time our `ssn:send` command runs following the `$send_at` date.

<small>
    <b>Note:</b> 
    if you really want to test this, set the "send_at" value to 1 minute from now (addMinute()) instead of 1 week. Then run `php artisan ssn:send` in your console in a minute.
</small>

[1]: https://carbon.nesbot.com/docs/ "Carbon"