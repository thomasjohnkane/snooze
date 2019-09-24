<?php

namespace Thomasjohnkane\Snooze\Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Thomasjohnkane\Snooze\Traits\ScheduledNotifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, Notifiable, ScheduledNotifiable;

    protected $fillable = ['name', 'email', 'password'];
}
