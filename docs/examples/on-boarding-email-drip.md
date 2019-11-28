[< Back to main README.md](https://github.com/thomasjohnkane/snooze)
# Simple On-boarding Email Drip

Let's send the following emails to our new Users:

1. Sign-Up Confirmation - will be sent 1 hour after they register
2. Welcome to the community - will be sent 3 days after they register
3. Upsell to Subscription - will be sent 1 week after they register

<hr />

## Add the trait to your user model
Add the `SnoozeNotifiable` trait to your user model
```php
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

class User {
    use SnoozeNotifiable;
}
```
## Generate the Scheduled Notifications:

1. Create the "sign-up confirmation" notification: 

    `php artisan make:notification SignUpConfirmation --m`

2. Create the "welcome to the community" notification:

    `php artisan make:notification WelcomeToCommunity --m`

3. Create the "upsell subscription" notification:

    `php artisan make:notification UpsellSubscription --m`

## Schedule our notifications after sign-up:
1. Create a User Observer to watch for the sign-ups

    `php artisan make:observer UserObserver --model=User`
    
    * <small>Added `app/Observers/UserObserver.php`</small>
    * <small>You must register this observer. <a href="https://laravel.com/docs/5.7/eloquent#observers" target="_blank">Look here for help registering your UserObserver class</a></small>

2. Schedule the Notifications once a User is `created`

    ```
    // use Carbon\Carbon;

    public function created(User $user)
    {
        $now = Carbon::now();
   
        $user->notifyAt(new SignUpConfirmation($user), $now->addHour());
        $user->notifyAt(new WelcomeToCommunity($user), $now->addDays(3));
        $user->notifyAt(new UpsellSubscription($user), $now->addWeek());
    }
    ```

## Send the notifications

- Our notification will be saved in our `scheduled_notifications` table.
- The `snooze:send` command is scheduled by the package to run every minute by default. It will send the notifications for us when they are ready.
- All you need to do is make sure your "schedule:run" command is running as well.
- [Read here for more information on how to run your scheduler.](2)

[1]: https://carbon.nesbot.com/docs/ "Carbon"
[2]: https://laravel.com/docs/5.7/scheduling#introduction "Configure Laravel Scheduler"
