[< Back to main README.md](https://github.com/thomasjohnkane/laravel-scheduled-notifications)
# Using with Existing Notifications and Mailables

You need to make your existing notifications accept an array as the first property in the `__contruct` method. 

```
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
```
You can then pass the `notifiable` user as the first parameter to your mailable, and pass the `data` array as te second parameter.

```
public function toMail($notifiable)
{
    return (new DummyClassMailable($notifiable, $this->data))->to($notifiable->email);
}
```

You need to make your existing mailables accept the User as first property in the `__contruct` method, and `data` array as the second. 

```
protected $user;

protected $data;

/**
 * Create a new message instance.
 *
 * @return void
 */
public function __construct(\App\User $user = NULL, $data = NULL)
{
    $this->user = $user;
    $this->data = $data;
}
```

We then expose this data to your email view like this:

```
public function build()
{
    return $this->markdown('DummyView')->with([
        'user' => $this->user,
        'data' => $this->data
    ]);
}
```