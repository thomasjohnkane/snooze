<?php

namespace Thomasjohnkane\Snooze\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Thomasjohnkane\Snooze\Exception\NotificationCancelledException;
use Thomasjohnkane\Snooze\Exception\NotificationAlreadySentException;

class ScheduledNotification extends Model
{
    protected $table;

    protected $casts = [
        'sent' => 'boolean',
        'rescheduled' => 'boolean',
        'cancelled' => 'boolean',
        'data' => 'array',
    ];

    protected $dates = [
        'send_at',
    ];

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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('snooze.snooze_table');
    }

    /**
     * @return void
     * @throws NotificationAlreadySentException
     */
    public function cancel()
    {
        if ($this->sent) {
            throw new NotificationAlreadySentException('Cannot Cancel. Notification already sent.', 1);
        }

        $this->cancelled = true;
        $this->save();
    }

    /**
     * @param \DateTimeInterface|string $sendAt
     * @param bool                      $force
     *
     * @return self
     * @throws NotificationAlreadySentException
     * @throws NotificationCancelledException
     */
    public function reschedule($sendAt, $force = false)
    {
        if (! $sendAt instanceof \DateTimeInterface) {
            $sendAt = Carbon::parse($sendAt);
        }

        if (($this->sent || $this->cancelled) && $force) {
            return $this->scheduleAgainAt($sendAt);
        }

        if ($this->sent) {
            throw new NotificationAlreadySentException('Cannot Reschedule. Date format is incorrect.', 1);
        }

        if ($this->cancelled) {
            throw new NotificationCancelledException('Cannot Reschedule. Notification cancelled.', 1);
        }

        $this->send_at = $sendAt;
        $this->rescheduled = true;
        $this->save();

        return $this;
    }

    /**
     * @param \DateTimeInterface|string $sendAt
     *
     * @return self
     */
    public function scheduleAgainAt($sendAt)
    {
        if (! $sendAt instanceof \DateTimeInterface) {
            $sendAt = Carbon::parse($sendAt);
        }

        $notification = $this->replicate();

        $notification->fill([
            'send_at' => $sendAt,
            'sent' => false,
            'rescheduled' => false,
            'cancelled' => false,
        ]);

        $notification->save();

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
