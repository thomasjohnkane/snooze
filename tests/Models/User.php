<?php

namespace Thomasjohnkane\Snooze\Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, Notifiable, SnoozeNotifiable;

    protected $fillable = ['name', 'email', 'password'];
}
