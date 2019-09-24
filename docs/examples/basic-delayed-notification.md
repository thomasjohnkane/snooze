[< Back to main README.md](https://github.com/thomasjohnkane/snooze)
# Basic Delayed Notification Example (1 week)

1. Add the `SnoozeNotifiable` trait to your notifiable model
2. Create the basic notification: `php artisan make:notification OneWeekAfterNotice`
3. Get the datetime for when we should send the notification
    * `$sendAt = Carbon::now()->addDays(7)` // 7 days from now
    * Here is a reference to [Carbon][1] if you need help creating future dates
    * <small>Note: Make sure you have imported carbon with `use Carbon\Carbon;` at the top of your file</small>

4. Schedule the notification for a notifiable User
```php
Auth::user()->notifyAt(new OneWeekAfterNotice(), $sentAt);
```

4. Our notification will be saved in our `scheduled_notifications` table, and will be sent the first time our `snooze:send` command runs following the `$send_at` date.

<small>
    <b>Note:</b> 
    If you want to test, set the "sendAt" value to  now (Carbon::now()) instead of 1 week. Then run `php artisan snooze:send` in your console.
</small>

[1]: https://carbon.nesbot.com/docs/ "Carbon"
