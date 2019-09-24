Laravel Snooze
=================================

> Schedule future notifications and reminders in Laravel

<p align="center">
    <img src="./snooze-logo-v1.png" />
</p>

[![Build Status](https://travis-ci.org/thomasjohnkane/snooze.svg?branch=master)](https://travis-ci.org/thomasjohnkane/snooze)
[![styleci](https://styleci.io/repos/173246329/shield)](https://styleci.io/repos/173246329)

[![Latest Stable Version](https://poser.pugx.org/thomasjohnkane/snooze/v/stable)](https://packagist.org/packages/thomasjohnkane/snooze)
[![Total Downloads](https://poser.pugx.org/thomasjohnkane/snooze/downloads)](https://packagist.org/packages/thomasjohnkane/snooze)
[![Latest Unstable Version](https://poser.pugx.org/thomasjohnkane/snooze/v/unstable)](https://packagist.org/packages/thomasjohnkane/snooze)
[![License](https://poser.pugx.org/thomasjohnkane/snooze/license)](https://packagist.org/packages/thomasjohnkane/snooze)

### Why use this package?
- Ever wanted to schedule a <b>future</b> notification to go out at a specific time? (was the delayed queue option not enough?) 
- Want a simple on-boarding email drip?
- How about <b>recurring</b> notifications to go out monthly, weekly, daily?

#### Common use cases
- Reminder system (1 week before appt, 1 day before, 1 hour before, etc)
- Follow-up surveys (2 days after purchase)
- On-boarding Email Drips (Welcome email after sign-up, additional tips after 3 days, upsell offer after 7 days)
- Birthday Emails

## Installation

Install via composer
```bash
composer require thomasjohnkane/snooze
```
```bash
php artisan migrate
```
*For Laravel < 5.5:* [Register Service Provider && Facade][1]

### Publish Configuration File

```bash
php artisan vendor:publish --provider="Thomasjohnkane\Snooze\ServiceProvider" --tag="config"
```

## Usage

#### Using the model trait
Snooze provides a trait for your model, similar to the standard `Notifiable` trait. 
It adds a `notifyAt()` method to your model to schedule notifications.

```php
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

class User extends Model {
    use SnoozeNotifiable;

    // ...
}

// Schedule a birthday notification
$user()->notifyAt(new BirthdayNotification, Carbon::parse($user->birthday));

// Schedule for a week from now
$user()->notifyAt(new NewYearNotification, Carbon::now()->addDays(7));

// Schedule for new years eve
$user()->notifyAt(new NewYearNotification, Carbon::parse('last day of this year'));
```

#### Using the ScheduledNotification::create helper
You can also use the `create` method on the `ScheduledNotification`. 
```php
ScheduledNotification::create(
     Auth::user(), // Target
     new ScheduledNotificationExample($order), // Notification
     Carbon::now()->addHour() // Send At
]);
```

This is also useful for scheduling anonymous notifications (routed direct, rather than on a model).
```php
$target = (new AnonymousNotifiable)
    ->route('mail', 'hello@example.com')
    ->route('sms', '56546456566');

ScheduledNotification::create(
     $target, // Target
     new ScheduledNotificationExample($order), // Notification
     Carbon::now()->addDay() // Send At
]);
```

#### An important note about scheduling the `snooze:send` command

Creating a scheduled notification will add the notification to the database. It will be sent by running `snooze:send` command at (or after) the stored `sendAt` time. 

The `snooze:send` command is scheduled to run every minute by default. You can change this value (`sendFrequency`) in the published config file. Available options are `everyMinute`, `everyFiveMinutes`, `everyTenMinutes`, `everyFifteenMinutes`, `everyThirtyMinutes`, `hourly`, and `daily`.

The only thing you need to do is make sure `schedule:run` is also running. You can test this by running `php artisan schedule:run` in the console. [To make it run automatically, read here][6].

### Setting the send tolerance

If your scheduler stops working, a backlog of scheduled notifications will build up. To prevent users receiving all of 
the old scheduled notifications at once, the command will only send mail within the configured tolerance. 
By default this is set to 24 hours, so only mail scheduled to be sent within that window will be sent. This can be
configured in the `snooze.php` config file. 

#### Detailed Examples

- [Delayed Notifcation (1 week)][3]
- [Simple On-boarding Email Drip][5]
- [Exposing Custom Data to the Notification/Email][4]

**Cancelling Scheduled Notifications**

```php
$notification->cancel();
```
<small><b>Note:</b> you cannot cancel a notification that has already been sent.</small>

**Rescheduling Scheduled Notifications**

```php
$rescheduleAt = Carbon::now()->addDay(1)

$notification->reschedule($rescheduleAt)
```
<small><b>Note:</b> you cannot reschedule a notification that has already been sent or cancelled.</small>
<small>If you want to duplicate a notification that has already been sent or cancelled, pass a truthy second parameter along with the new send date; `reschedule($date, true)`, or use the `scheduleAgainAt($date)` method shown below.</small>

**Duplicate a Scheduled Notification to be sent again**

```php
$notification->scheduleAgainAt($newDate); // Returns the new (duplicated) $notification
```

**Check a scheduled notification's status**
```php
// Check if a notification is already cancelled

$result = $notification->isCancelled(); // returns a bool

// Check if a notification is already sent

$result = $notification->isSent(); // returns a bool
```

## Project Roadmap

- [x] Initial setup
    - [x] Create boilerplate template
    - [x] Add Readme and project roadmap
    - [x] Create data model and add DB migration for table
    - [x] Add table name to package config
    - [x] Write initital `snooze:send` command to run
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
    - [ ] Make sure the notification exists before scheduling?
 
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

- [ ] Change model/facade name to "SnoozeNotification" instead of "ScheduledNotification"???

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

## Run Tests

```bash
composer test
```

## Security

If you discover any security related issues, please email instead of using the issue tracker.

## Contributing

1. Fork it (<https://github.com/thomasjohnkane/snooze/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

## Credits

- [Thomas Kane && Flux Bucket](https://github.com/thomasjohnkane)
- [Atymic](https://github.com/atymic)
- [All contributors](https://github.com/thomasjohnkane/snooze/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).

[1]: ./docs/register-provider-and-facade.md "Register Service Provider && Facade"
[2]: https://carbon.nesbot.com/docs/ "Carbon"
[3]: ./docs/examples/basic-delayed-notification.md "Delayed 1 Week Example"
[4]: ./docs/examples/custom-data-example.md "Custom Data Example"
[5]: ./docs/examples/on-boarding-email-drip.md  "On-boarding Drip Example"
[6]: https://laravel.com/docs/5.7/scheduling#introduction "Configure Laravel Scheduler"
[7]: https://laravel.com/docs/5.7/scheduling#introduction "Generators"
