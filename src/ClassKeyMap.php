<?php

namespace Thomasjohnkane\Snooze;

use InvalidArgumentException;
use Thomasjohnkane\Snooze\Exception\ClassKeyViolationException;

class ClassKeyMap
{
    public static array $map = [
        'anonymous-notifiable' => AnonymousNotifiable::class,
    ];

    public static function map(array $map = null, $merge = true): array
    {
        if (is_array($map)) {
            static::$map = $merge && static::$map
                ? $map + static::$map : $map;
        }

        if (count(self::$map) !== count(array_unique(self::$map))) {
            throw new InvalidArgumentException('Duplicate values found in class map');
        }

        return static::$map;
    }

    public static function getKey(mixed $class): string
    {
        if (! is_string($class)) {
            $class = get_class($class);
        }

        $key = array_search($class, self::$map);

        if (! $key) {
            throw new ClassKeyViolationException();
        }

        return $key;
    }

    public static function getClass(string $key): ?string
    {
        $class = self::$map[$key];

        if (! $class) {
            throw new ClassKeyViolationException();
        }

        return $class;
    }
}
