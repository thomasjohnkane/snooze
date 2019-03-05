<?php

namespace Thomasjohnkane\ScheduledNotifications\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Snotification extends Model
{
    protected $table;

    protected $casts = [
        'data' => 'array',
    ];

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'send_at',
        'sent',
        'rescheduled',
        'cancelled',
        'created_at',
        'updated_at',
    ];

    // Set table name from config
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('scheduled-notifications.ssn_table');
    }

    public function cancel()
    {
        try {
            if ($this->sent == 1) {
                throw new \Exception('Cannot Cancel. Notification already sent.', 1);
            }

            $this->cancelled = 1;
            $this->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function cancelled()
    {
        return (bool) $this->cancelled;
    }

    public function sent()
    {
        return (bool) $this->sent;
    }

    public function reschedule($send_at, $force = false)
    {
        try {
            if (Carbon::createFromFormat('Y-m-d H:i:s', $send_at) === false) {
                throw new \Exception('Cannot Reschedule. Date format is incorrect.', 1);
            } elseif ($this->sent == 1) {
                throw new \Exception('Cannot Reschedule. Notification already sent.', 1);
            } elseif ($this->cancelled == 1) {
                throw new \Exception('Cannot Reschedule. Notification cancelled.', 1);
            }

            $this->send_at = $send_at;
            $this->rescheduled = 1;
            $this->save();
        } catch (\InvalidArgumentException $e) {
            if ($force) {
                return $this->scheduleAgainAt($send_at);
            }

            Log::error('Cannot Reschedule. Invalid date format provided.');

            return false;
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function scheduleAgainAt($send_at)
    {
        try {
            if (Carbon::createFromFormat('Y-m-d H:i:s', $send_at) === false) {
                throw new \Exception('Cannot Reschedule. Date format is incorrect.', 1);
            }

            $notification = $this->replicate();
            $notification->send_at = $send_at;
            $notification->sent = 0;
            $notification->rescheduled = 0;
            $notification->cancelled = 0;
            $notification->save();
        } catch (\InvalidArgumentException $e) {
            Log::error('Cannot Reschedule. Invalid date format provided.');

            return false;
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return $notification;
    }

    public function scopeHasData($query, $key, $value)
    {
        if (! $key) {
            $key = 'data';
        } else {
            $key = "data->{$key}";
        }

        return $query->where($key, $value);
    }

    public function scopeWhereDataContains($query, $key, $value)
    {
        if (! $key) {
            $key = 'data';
        } else {
            $key = "data->{$key}";
        }

        return $query->whereJsonContains($key, $value);
    }
}
