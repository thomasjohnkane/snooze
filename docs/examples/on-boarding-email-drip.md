[< Back to main README.md](../../)
# Simple On-boarding Email Drip

Let's send the following emails to our new Users:

1. Sign-Up Confirmation - will be sent 1 hour after they register
2. Welcome to the community - will be sent 3 days after they register
3. Upsell to Subscription - will be sent 1 week after they register

<hr />
## Generate the Scheduled Notifications:

1. Create the "sign-up confirmation" notification: 

    `php artisan make:notification:scheduled SignUpConfirmation --mm`
    
    <b>Quick Notes:</b>

    * <small>This uses a custom generator. You could do this with the normal Laravel generators though if you prefer</small>
    * <small>The --mm flag tells the generator to also create and connect a Mailable class and Markdown email template for us</small>
    * <small>The markdown emails are in the `resources/views/emails` folder by default</small>

2. Create the "welcome to the community" notification:

    `php artisan make:notification:scheduled WelcomeToCommunity --mm`

3. Create the "upsell subscription" notification:

    `php artisan make:notification:scheduled UpsellSubscription --mm`

## Schedule our notifications after sign-up:
1. Create a User Observer to watch for the sign-ups

    `php artisan make:observer UserObserver --model=User`
    
    * <small>Added `app/Observers/UserObserver.php`</small>
    * <small>You must register this observer. <a href="https://laravel.com/docs/5.7/eloquent#observers" target="_blank">Look here for help registering your UserObserver class</a></small>

2. Schedule the Notifications once a User is `created`

    ```
    // use Thomasjohnkane\ScheduledNotifications\Models\SsNotification;
    // use Carbon\Carbon;

    public function created(User $user)
    {
        $now = Carbon::now();

        $notification = SsNotification::insert([
            [
                'user_id'    => $user->id,
                'send_at'    => $now->copy()->addHour()->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\SignUpConfirmation',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id'    => $user->id,
                'send_at'    => $now->copy()->addDays(3)->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\WelcomeToCommunity',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'user_id'    => $user->id,
                'send_at'    => $now->copy()->addWeek()->format('Y-m-d H:i:s'),
                'type'       => 'App\Notifications\UpsellSubscription',
                'created_at' => $now,
                'updated_at' => $now

            ],
        ]);
    }
    ```

## Send the notifications

- Our notification will be saved in our `scheduled_notifications` table.
- The `ssn:send` command is scheduled by the package to run every minute by default. It will send the notifications for us when they are ready.
- All you need to do is make sure your "schedule:run" command is running as well.
- [Read here for more information on how to run your scheduler.](2)

[1]: https://carbon.nesbot.com/docs/ "Carbon"
[2]: https://laravel.com/docs/5.7/scheduling#introduction "Configure Laravel Scheduler"