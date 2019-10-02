<?php

namespace Thomasjohnkane\Snooze;

use Carbon\Carbon;
use DateTimeInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notification;
use Thomasjohnkane\Snooze\Exception\SchedulingFailedException;
use Thomasjohnkane\Snooze\Exception\NotificationCancelledException;
use Thomasjohnkane\Snooze\Exception\NotificationAlreadySentException;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;

class ScheduledNotification
{
    /** @var ScheduledNotificationModel */
    private $scheduleNotificationModel;

    public function __construct(ScheduledNotificationModel $scheduleNotificationModel)
    {
        $this->scheduleNotificationModel = $scheduleNotificationModel;
    }

    /**
     * @param object            $notifiable
     * @param Notification      $notification
     * @param DateTimeInterface $sendAt
     *
     * @return self
     * @throws SchedulingFailedException
     */
    public static function create(
        object $notifiable,
        Notification $notification,
        DateTimeInterface $sendAt
    ): self {
        if (! method_exists($notifiable, 'notify')) {
            throw new SchedulingFailedException(sprintf('%s is not notifiable', get_class($notifiable)));
        }

        $modelClass = self::getScheduledNotificationModelClass();

        return new self($modelClass::create([
            'type' => get_class($notification),
            'target' => Serializer::create()->serializeNotifiable($notifiable),
            'notification' => Serializer::create()->serializeNotification($notification),
            'send_at' => $sendAt,
        ]));
    }

    public static function find(int $scheduledNotificationId): ?self
    {
        $modelClass = self::getScheduledNotificationModelClass();

        $model = $modelClass::find($scheduledNotificationId);

        return $model ? new self($model) : null;
    }

    public static function findByType(string $notificationClass, bool $includeSent = false): Collection
    {
        $modelClass = self::getScheduledNotificationModelClass();

        if ($includeSent) {
            return self::collection($modelClass::whereType($notificationClass)->get());
        }

        return self::collection($modelClass::whereType($notificationClass)->whereNull('sent_at')->get());
    }

    public static function all(bool $includeSent = false): Collection
    {
        $modelClass = self::getScheduledNotificationModelClass();

        if ($includeSent) {
            return self::collection($modelClass::get());
        }

        return self::collection($modelClass::whereNull('sent_at')->get());
    }

    /**
     * @param DateTimeInterface|string $sendAt
     * @param bool                     $force
     *
     * @return self
     * @throws NotificationAlreadySentException
     * @throws NotificationCancelledException
     */
    public function reschedule($sendAt, $force = false): self
    {
        return new self($this->scheduleNotificationModel->reschedule($sendAt, $force));
    }

    /**
     * @param DateTimeInterface|string $sendAt
     *
     * @return self
     */
    public function scheduleAgainAt($sendAt): self
    {
        return new self($this->scheduleNotificationModel->scheduleAgainAt($sendAt));
    }

    public function cancel(): void
    {
        $this->scheduleNotificationModel->cancel();
    }

    public function sendNow(): void
    {
        $this->scheduleNotificationModel->send();
    }

    public function isSent(): bool
    {
        return $this->scheduleNotificationModel->sent_at !== null;
    }

    public function isCancelled(): bool
    {
        return $this->scheduleNotificationModel->cancelled_at !== null;
    }

    public function isRescheduled(): bool
    {
        return $this->scheduleNotificationModel->rescheduled_at !== null;
    }

    public function getId()
    {
        return $this->scheduleNotificationModel->id;
    }

    public function getType()
    {
        return $this->scheduleNotificationModel->type;
    }

    public function getSentAt()
    {
        return $this->scheduleNotificationModel->sent_at;
    }

    public function getCancelledAt()
    {
        return $this->scheduleNotificationModel->cancelled_at;
    }

    public function getRescheduledAt()
    {
        return $this->scheduleNotificationModel->rescheduled_at;
    }

    /**
     * @return Carbon|CarbonImmutable
     */
    public function getSendAt()
    {
        return $this->scheduleNotificationModel->send_at;
    }

    /**
     * @return Carbon|CarbonImmutable
     */
    public function getCreatedAt()
    {
        return $this->scheduleNotificationModel->created_at;
    }

    /**
     * @return Carbon|CarbonImmutable
     */
    public function getUpdatedAt()
    {
        return $this->scheduleNotificationModel->updated_at;
    }

    private static function getScheduledNotificationModelClass(): string
    {
        return config('snooze.model') ?? ScheduledNotificationModel::class;
    }

    private static function collection(Collection $models): Collection
    {
        return $models->map(function (ScheduledNotificationModel $model) {
            return new self($model);
        });
    }
}
