<?php

namespace Thomasjohnkane\Snooze;

use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class Serializer
{
    use SerializesAndRestoresModelIdentifiers;

    public static function create(): self
    {
        return new self();
    }

    public function serializeNotifiable(object $notifiable): string
    {
        return serialize(self::getSerializedPropertyValue(clone $notifiable));
    }

    public function serializeNotification(Notification $notification): string
    {
        return base64_encode(serialize(clone $notification));
    }

    public function unserializeNotifiable(string $serialized)
    {
        return $this->getRestoredPropertyValue(unserialize($serialized));
    }

    public function unserializeNotification(string $serialized)
    {
        return unserialize(base64_decode($serialized));
    }
}
