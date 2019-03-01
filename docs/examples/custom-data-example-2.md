[< Back to main README.md](../../README.md)
# Notification with Custom Data (2 Minutes)

1. Create the basic notification: `php artisan make:notification:scheduled TwoMinuteTestNotice --mail`

2. Update the `toMail` method of this new notification:
```php
    // app/Notifications/TwoMinuteTestNotice.php

    protected $data;

    public function __construct($data = NULL)
    {
        $this->data = $data;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                ->subject('Hi, ' . $notifiable->name . ' Two Minute Test Notification...')
                ->line($this->data['message'])
                ->action('Clickity Click Bait', url($this->data['action_link']))
                ->line('Thank you for using the SSN package!');
    }
```

2. Get the date of when we should send the notification
    * `$send_at = Carbon::now()->addMins(2)->format('Y-m-d H:i:s')` // 2 minutes from now

3. Schedule the notification for a notifiable User
```php
// use Thomasjohnkane\SimpleScheduledNotification\SsNotification;

$data = [
    'action_link' => 'this-variable-link',
    'message' => 'The link below should navigate to "/this-variable-link"' which should be a 404 error.",
];

$notification = new SsNotification;
$notification->user_id = \Auth::id();
$notification->send_at = $send_at;
$notification->type = 'App\Notifications\TwoMinuteTestNotice';
$notification->data = $data;
$notification->save();
```

4. Run `php artisan ssn:send` in 2 minutes. Then check your email to see the variables. Woo!
