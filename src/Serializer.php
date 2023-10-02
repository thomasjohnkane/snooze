<?php

declare(strict_types=1);

namespace Thomasjohnkane\Snooze;

use Thomasjohnkane\Snooze\Concerns\ClassMapSerializable;

class Serializer
{
    public function serialize(object $notifiable): string
    {
        return json_encode([
            'key' =>  ClassKeyMap::getKey(get_class($notifiable)),
            'payload' => $notifiable->toSerializedPayload(),
        ]);
    }

    public function unserialize(string $serialized)
    {
        $decoded = json_decode($serialized, true);
        $class = ClassKeyMap::getClass($decoded['key']);

        /** @var ClassMapSerializable $class */
        return $class::fromSerializedPayload($decoded['payload']);
    }
}
