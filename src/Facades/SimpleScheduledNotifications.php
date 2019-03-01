<?php

namespace Thomasjohnkane\SimpleScheduledNotifications\Facades;

use Illuminate\Support\Facades\Facade;

class SimpleScheduledNotifications extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'simple-scheduled-notifications';
    }
}
