<?php

namespace Thomasjohnkane\Snooze\Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Thomasjohnkane\Snooze\Concerns\ClassMapSerializable;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

class User extends Model implements AuthenticatableContract, ClassMapSerializable
{
    use Authenticatable, Notifiable, SnoozeNotifiable;

    protected $fillable = ['name', 'email', 'password'];
}
