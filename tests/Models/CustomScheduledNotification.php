<?php

namespace Thomasjohnkane\Snooze\Tests\Models;

use Exception;
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

class CustomScheduledNotification extends ScheduledNotification
{
    public function send(): void
    {
        throw new Exception('Custom send method');
    }
}
