<?php

namespace Thomasjohnkane\Snooze\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Thomasjohnkane\Snooze\Events\NotificationInterrupted;
use Thomasjohnkane\Snooze\Events\NotificationSent;
use Thomasjohnkane\Snooze\Exception\NotificationAlreadySentException;
use Thomasjohnkane\Snooze\Exception\NotificationCancelledException;
use Thomasjohnkane\Snooze\Serializer;

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
        'target_id',
        'target_type',
        'target',
        'notification_type',
        'notification',
        'send_at',
        'sent_at',
        'rescheduled',
        'cancelled',
        'created_at',
        'updated_at',
        'meta',
    ];

    protected $attributes = [
        'sent_at' => null,
        'rescheduled_at' => null,
        'cancelled_at' => null,
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('snooze.table');
        $this->serializer = app(Serializer::class);
    }

    public function send(): void
    {
        if ($this->cancelled_at !== null) {
            throw new NotificationCancelledException('Cannot Send. Notification cancelled.', 1);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Send. Notification already sent.', 1);
        }

        $notifiable = $this->serializer->unserialize($this->target);
        $notification = $this->serializer->unserialize($this->notification);

        if ($this->shouldInterrupt($notification, $notifiable)) {
            $this->cancel();
            event(new NotificationInterrupted($this));

            return;
        }

        $notifiable->notify($notification);

        event(new NotificationSent($this));

        $this->sent_at = Carbon::now();
        $this->save();
    }

    /**
     * @param  object|null  $notification
     * @param  object|null  $notifiable
     * @return bool
     */
    public function shouldInterrupt(?object $notification = null, ?object $notifiable = null): bool
    {
        if (! $notification) {
            $notification = $this->serializer->unserialize($this->notification);
        }

        if (! $notifiable) {
            $notifiable = $this->serializer->unserialize($this->target);
        }

        if (method_exists($notification, 'shouldInterrupt')) {
            return (bool) $notification->shouldInterrupt($notifiable);
        }

        return false;
    }

    /**
     * @return void
     *
     * @throws NotificationAlreadySentException
     */
    public function cancel(): void
    {
        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Cancel. Notification already sent.', 1);
        }

        $this->cancelled_at = Carbon::now();
        $this->save();
    }

    /**
     * @param  \DateTimeInterface|string  $sendAt
     * @param  bool  $force
     * @return self
     *
     * @throws NotificationAlreadySentException
     * @throws NotificationCancelledException
     */
    public function reschedule($sendAt, $force = false): self
    {
        if (! $sendAt instanceof \DateTimeInterface) {
            $sendAt = Carbon::parse($sendAt);
        }

        if (($this->sent_at !== null || $this->cancelled_at !== null) && $force) {
            return $this->scheduleAgainAt($sendAt);
        }

        if ($this->sent_at !== null) {
            throw new NotificationAlreadySentException('Cannot Reschedule. Notification Already Sent', 1);
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
     * @param  \DateTimeInterface|string  $sendAt
     * @return self
     */
    public function scheduleAgainAt($sendAt): self
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
