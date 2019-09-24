[< Back to main README.md](https://github.com/thomasjohnkane/snooze)
### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
Thomasjohnkane\ScheduledNotifications\ServiceProvider::class,
```

### Register Facade\

Register package facade in `config/app.php` in `aliases` section
```php
Thomasjohnkane\ScheduledNotifications\Facades\ScheduledNotification::class,
```
