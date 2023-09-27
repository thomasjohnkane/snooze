<?php

declare(strict_types=1);

namespace Thomasjohnkane\Snooze;

use InvalidArgumentException;
use Thomasjohnkane\Snooze\Concerns\ClassMapSerializable;
use Thomasjohnkane\Snooze\Exception\ClassMapSerializationException;

class Serializer
{
    public static array $classMap = [
        AnonymousNotifiable::class => 'anonymous-notifiable',
    ];

    public static function classMap(array $map = null, $merge = true): array
    {
        if (is_array($map)) {
            static::$classMap = $merge && static::$classMap
                ? $map + static::$classMap : $map;
        }

        if (count(self::$classMap) !== count(array_unique(self::$classMap))) {
            throw new InvalidArgumentException('Duplicate values found in class map');
        }

        return static::$classMap;
    }

    public function serialize(object $notifiable): string
    {
        $key = self::$classMap[get_class($notifiable)] ?? null;

        if (! $key) {
            throw new ClassMapSerializationException('No key found for class '.get_class($notifiable));
        }

        return json_encode([
            'key' => self::$classMap[get_class($notifiable)],
            'payload' => $notifiable->toSerializedPayload(),
        ]);
    }

    public function unserialize(string $serialized)
    {
        $decoded = json_decode($serialized, true);
        $class = array_search($decoded['key'], self::$classMap);

        if (! $class || is_int($class) || ! class_exists($class)) {
            throw new ClassMapSerializationException('No class found for '.$decoded['key']);
        }

        /** @var ClassMapSerializable $class */
        return $class::fromSerializedPayload($decoded['payload']);
    }
}
