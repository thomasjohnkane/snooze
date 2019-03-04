<?php

namespace Thomasjohnkane\ScheduledNotifications\Facades;

use Illuminate\Support\Facades\Facade;

class ScheduledNotifications extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'scheduled-notifications';
    }
}
