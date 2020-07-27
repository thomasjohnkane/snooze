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
        return str_replace("\0", "~~NULL_BYTE~~", serialize(clone $notification));
//        return base64_encode(serialize(clone $notification));
    }

    public function unserializeNotifiable(string $serialized)
    {
        return $this->getRestoredPropertyValue(unserialize($serialized));
    }

    public function unserializeNotification(string $serialized)
    {
        return unserialize(str_replace("~~NULL_BYTE~~", "\0", $serialized));;
//        return unserialize(base64_decode($serialized));
    }
}
