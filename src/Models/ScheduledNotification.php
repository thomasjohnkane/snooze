<?php

namespace Thomasjohnkane\Snooze\Models;

use Carbon\Carbon;
use Thomasjohnkane\Snooze\Serializer;
use Illuminate\Database\Eloquent\Model;
use Thomasjohnkane\Snooze\Exception\NotificationCancelledException;
use Thomasjohnkane\Snooze\Exception\NotificationAlreadySentException;

class ScheduledNotification extends Model
{
    /** @var string */
    protected $table;
    /** @var Serializer */
    protected $serializer;

    protected $dates = [
        'send_at',
        'sent_at',
        'rescheduled_at',
        'cancelled_at',
    ];

    protected $fillable = [
        'type',
        'target',
        'notification',
        'send_at',
        'sent',
        'rescheduled',
        'cancelled',
        'created_at',
        'updated_at',
    ];

    protected $attributes = [
        'sent_at' => null,
        'rescheduled_at' => null,
        'cancelled_at' => null,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('snooze.snooze_table');
        $this->serializer = Serializer::create();
    }

    public function send()
    {
        if ($this->cancelled_at !== null) {
            throw new NotificationCancelledException('Cannot Send. Notification cancelled.', 1);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Send. Notification already sent.', 1);
        }

        $notifiable = $this->serializer->unserializeNotifiable($this->target);
        $notification = $this->serializer->unserializeNotification($this->notification);

        $notifiable->notify($notification);

        $this->sent_at = Carbon::now();
        $this->save();
    }

    /**
     * @return void
     * @throws NotificationAlreadySentException
     */
    public function cancel()
    {
        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Cancel. Notification already sent.', 1);
        }

        $this->cancelled_at = Carbon::now();
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

        if (($this->sent_at !== null || $this->cancelled_at !== null) && $force) {
            return $this->scheduleAgainAt($sendAt);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Reschedule. Date format is incorrect.', 1);
        }

        if ($this->cancelled_at !== null) {
            throw new NotificationCancelledException('Cannot Reschedule. Notification cancelled.', 1);
        }

        $this->send_at = $sendAt;
        $this->rescheduled_at = Carbon::now();
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
            'sent_at' => null,
            'rescheduled_at' => null,
            'cancelled_at' => null,
        ]);

        $notification->save();

        return $notification;
    }
}
