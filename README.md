Laravel Scheduled Notifications
=================================

> Schedule future notifications and reminders in Laravel

<p align="center">
    <img width="50%"  src="./snotifications-logo.png" />
</p>

[![Build Status](https://travis-ci.org/melihovv/laravel-package-generator.svg?branch=master)](https://travis-ci.org/melihovv/laravel-package-generator)
[![styleci](https://styleci.io/repos/96041272/shield)](https://styleci.io/repos/96041272)

[![Latest Stable Version](https://poser.pugx.org/thomasjohnkane/laravel-scheduled-notifications/v/stable)](https://packagist.org/packages/thomasjohnkane/laravel-scheduled-notifications)
[![Total Downloads](https://poser.pugx.org/thomasjohnkane/laravel-scheduled-notifications/downloads)](https://packagist.org/packages/thomasjohnkane/laravel-scheduled-notifications)
[![Latest Unstable Version](https://poser.pugx.org/thomasjohnkane/laravel-scheduled-notifications/v/unstable)](https://packagist.org/packages/thomasjohnkane/laravel-scheduled-notifications)
[![License](https://poser.pugx.org/thomasjohnkane/laravel-scheduled-notifications/license)](https://packagist.org/packages/thomasjohnkane/laravel-scheduled-notifications)

##### Why use this package?
- Ever wanted to schedule a <b>future</b> notification to go out at a specific time? (was the delayed queue option not enough?) 
- Want a simple on-boarding email drip?
- How about <b>recurring</b> notifications to go out monthly, weekly, daily?

The goal is convention over configuration. This package largly just provides an opinionated architecture and generators for existing Laravel functionality. Hope this makes your life easier like it did mine!

### Common use cases
- Reminder system (1 week before appt, 1 day before, 1 hour before, etc)
- Follow-up surveys (2 days after purchase)
- On-boarding Email Drips (Welcome email after sign-up, additional tips after 3 days, upsell offer after 7 days)
- Monthly Reports (or any other time-based notifications)

## Installation

Install via composer
```bash
composer require thomasjohnkane/scheduled-notifications
```
```bash
php artisan migrate
```
*For Laravel < 5.5:* [Register Service Provider && Facade][1]

### Publish Configuration File

```bash
php artisan vendor:publish --provider="Thomasjohnkane\ScheduledNotifications\ServiceProvider" --tag="config"
```
<small>Note: The only important config value here is the table name. If you need to change this, you need to do it before migrating.</small>

### Run Tests

```bash
cd path/to/vendor/thomasjohnkane/scheduled-notifications
composer install
vendor/bin/phpunit
```
## Usage

#### Basic Use

Send "Example" notification to the authenticated user, in an hour...with some custom data
```
// use Thomasjohnkane\ScheduledNotifications\Models\Snotification;

Snotification::create([
    'user_id' => Auth::id(),
    'send_at' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
    'type'    => 'App\Notifications\ScheduledNotificationExample',
    'data'    => ['order_id' => 10]
]);
```
<small>Note: "data" is an optional array. It is exposed to the notification/mailable if provided.</small>

#### An important note about scheduling the `ssn:send` commmand

Creating a Scheduled Notification (Snotification Model), as we did above, will add the notification to the database. It will be sent by running `ssn:send` command at, or after, the stored `send_at` time. 

The `ssn:send` command is scheduled to run every minute by default. You can change this value (send_frequency) in the published config file. Available options are `everyMinute`, `everyFiveMinutes`, `everyTenMinutes`, `everyFifteenMinutes`, `everyThirtyMinutes`, `hourly`, and `daily`.

The only thing you need to do is make sure `schedule:run` is also running. You can test this by running `php artisan schedule:run` in the console. [To make it run automatically, read here][6].

#### Detailed Examples

- [Delayed Notifcation (1 week)][3]
- [Simple On-boarding Email Drip][5]
- [Exposing Custom Data to the Notification/Email][4]

**Using with existing Notifications and Mailable**

We recommend using the Snotification generators (see below).

However, if you have existing notifications you'd like to schedule, all you need to do is accept the `data` array in your notification. [Read more here][8]

**Cancelling Scheduled Notifications**

```
$notification->cancel(); // Returns TRUE or FALSE
```
<small><b>Note:</b> you cannot cancel a notification that has already been sent.</small>

**Rescheduling Scheduled Notifications**

```
$reschedule_at = Carbon::now()->format('Y-m-d H:i:s'); // Must be in this datetime format

$notification->reschedule($reschedule_at); // Returns TRUE or FALSE
```
<small><b>Note:</b> you cannot reschedule a notification that has already been sent or cancelled.</small>
<small>If you want to duplicate a notification that has already been sent or cancelled, pass a truthy second parameter along with the new send date; `reschedule($date, TRUE)`, or use the `scheduleAgainAt($date)` method shown below.</small>

**Duplicate a Scheduled Notification to be sent again**

```
$notification->scheduleAgainAt($new_date); // Returns the new (duplicate) $notification instance
```

**Check a scheduled notification's status**
```
// Check if a notification is already cancelled

$result = $notification->cancelled(); // Returns TRUE or FALSE

// Check if a notification is already sent

$result = $notification->sent(); // Returns TRUE or FALSE
```

**Search Notifications by custom data**

I implemented helper methods to query the `data` JSON column. They wrap the normal Eloquent `where` and `whereJsonContains` methods.

###### Query the data column:

If a notification is saved with the following custom data:
```
$data = [
    'booking_id'    => 1,
    'property_name' => 'Hotel Down The Road'
];
```

It could be returned by a query like this:

```
Snotification::whereData('booking_id', 1)->get();
```
<small>Note: this would be the same as doing this: `Snotification::where('options->languages', ['en', 'de'])->get()`</small>
###### Check nested data

If a notification is saved with the following custom data:
```
$data = [
    'reservation' => [
        'start' => '2019-06-10',
        'end'   => '2019-06-12'
    ]
];
```

It would be returned by this query:
```
Snotification::whereData('reservation->start', '2019-06-10')->get();
```
###### Using JSON contains
```
Snotification::whereDataContains('en')->get();
```
For information and examples on Laravel's `whereJsonContains` method <a href="https://laravel.com/docs/5.7/queries#json-where-clauses" target="__blank">look here</a>.

#### Scheduled Notification Generator

`php artisan make:notification:scheduled NotificationName {--mail?} {-mm?}`

**Options:**

`--mail`
Generates a Mailable class that accepts the "data" array and User as parameters from the notification and is automatically added to the `toMail` method of the notification

`--mm` Generates the same Mailable class AND a markdown email template with access to `$data` and `$user` variables.

<small>
Note: Notification, Mailable, and Markdown are all placed in their normal folders. The markdown templates are placed in a "scheduled-emails" subfolder.

- `app/Notifications/NotificationName.php`
- `app/Mail/NotificationNameMailable.php`
- `app/resources/views/scheduled-emails/notification-name.blade.php`
</small>

## Project Roadmap

- [x] Initial setup
    - [x] Create boilerplate template
    - [x] Add Readme and project roadmap
    - [x] Create data model and add DB migration for table
    - [x] Add table name to package config
    - [x] Write initital `ssn:send` command to run
    - [x] Schedule the send command automatically
    - [x] Add send frequency to config file

- [x] Basic Usage
    - [x] Write example(s) for how to create notifications
    - [x] Link to articles for running the command and such...
    - [x] Add method for cancelling scheduled notifications
        - [x] Handle already sent notifications
    - [x] Add method for rescheduling notifications
        - [x] Handle notifications that already sent, or are cancelled?
    - [x] Add scope for searching data column
        - [x] hasData() and orHasData() using arrow notation with data pre-appended
        - [x] whereDataContains() using whereJsonContains with data assumed
        - [x] Add usage examples for the scopes
 
- [x] Generators
    - [x] Create generator for scheduled notification stub
    - [x] Add generators for linked mailable and email view (options)
        - [x] -mail, -mm (mail + markdown)
    - [x] Add instructions for using generators

- [ ] Tests
    - [x] Get basic coverage for "create"
    - [x] Cancel
    - [x] Reschedule
    - [x] scheduleAgainAt (duplocate)
    - [ ] Generators
    - [ ] Send command

- [x] Add logo and those badges

- [x] Submit V1 to Packagist

- [ ] Admin UI
    - [ ] Show tab for past notifications
    - [ ] Show tab for scheduled notifications
    - [ ] Give CRUD options for scheduled notifications
    - [ ] Create as a Nova Package

- [ ] Create generator for new "interval notification command"
    - [ ] Include available input flags
        - frequency; monthly, weekly, daily, etc
        - notifiable model type
            - default to `\App\User`
        - initial each loop to send notification


## Security

If you discover any security related issues, please email 
instead of using the issue tracker.

## Contributing

1. Fork it (<https://github.com/thomasjohnkane/laravel-scheduled-notifications/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

## Credits

- [Thomas Kane && Flux Bucket](https://github.com/thomasjohnkane/laravel-scheduled-notifications)
- [All contributors](https://github.com/thomasjohnkane/laravel-scheduled-notifications/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).


[1]: ./docs/register-provider-and-facade.md "Register Service Provider && Facade"
[2]: https://carbon.nesbot.com/docs/ "Carbon"
[3]: ./docs/examples/basic-delayed-notification.md "Delayed 1 Week Example"
[4]: ./docs/examples/custom-data-example.md "Custom Data Example"
[5]: ./docs/examples/on-boarding-email-drip.md  "On-boarding Drip Example"
[6]: https://laravel.com/docs/5.7/scheduling#introduction "Configure Laravel Scheduler"
[7]: https://laravel.com/docs/5.7/scheduling#introduction "Generators"
[8]: ./docs/using-with-existing-notifications.md "Using With Existing Notifications"
