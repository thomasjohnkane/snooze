[< Back to main README.md](https://github.com/thomasjohnkane/laravel-snooze)
# Exposing Custom Data to Notification/Email

The important thing here is the "data" field on the `SsNotification` model.

The field must be a valid array or `NULL`. It is stored in a JSON field. Therefore your database must be able to support JSON columns.
##### Create our example Notification

`php artisan make:notification:scheduled OrderReceipt --mm`

##### Pass the data while creating the notification:

```
// use Thomasjohnkane\ScheduledNotifications\Models\SsNotification;
// use Carbon/Carbon;

// Create our custom data array
$data = [
    'order_id'    => 10,
    'order_total' => '$4,984.75',
    'order_items' => [
        [
            'id'       => 1,
            'name'     => 'Mustard',
            'price'    => '$768.49',
            'quantity' => 1
        ],
        [
            'id'       => 1,
            'name'     => 'Toy Mustang',
            'price'    => '$2108.13',
            'quantity' => 2
        ],
    ],
    'payment_type' => 'credit'
]

// Create Scheduled Notification, with our data
$notification = SScheduledNotification::create([
    'user_id' => Auth::id(),
    'send_at' => Carbon::now()->addHour2(4)->format('Y-m-d H:i:s'),
    'type'    => 'App\Notifications\OrderReceipt',
    'data'    => $data
]);
```

##### Access the data in the Notification class

The constructor in our Notification class will look like this by default:

```
class OrderReceipt extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data = NULL)
    {
        $this->data = $data;
    }
    ...
}
```

So we now have access to the `data` array using `$this->data` anywhere in this class.

```
// Examples:

$order_id      = $this->data['order_id'];
$total         = $this->data['order_total'];
$items         = collect($this->data['order_items']);
$mustard_count = $this->data['order_items'][0]['quantity'];
```

Our `toMail` method will look like this by default:

```
public function toMail($notifiable)
{
    return (new OrderReceiptMailable($notifiable, $this->data)->to($notifiable->email);
}
```

So this will pass our `data` array to our Mailable class for us!

##### Access the data in the Mailable class

The constructor method in our Mailable will look like this by default:

```
public function __construct(\App\User $user, $data = NULL)
{
    $this->user = $user;
    $this->data = $data;
}
```

So now we can access our notifiable user and our custom data anywhere in our Mailable class.

Or build class will look like this:

```
public function build()
{
    return $this->markdown('scheduled-emails.order-receipt')->with([
        'user' => $this->user,
        'data' => $this->data
    ]);
}
```

This will pass our user and data to the email view as well!

##### Access the data in the email view

Our email view will have access to `$data` and `$user`

```
// Examples:

$user->name;

$data['order_total'];
```

Here is another example with custom data:
