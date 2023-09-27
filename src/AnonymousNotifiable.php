<?php

namespace Thomasjohnkane\Snooze;

use Illuminate\Notifications\AnonymousNotifiable as BaseAnonymousNotifiable;
use Thomasjohnkane\Snooze\Concerns\ClassMapSerializable;

class AnonymousNotifiable extends BaseAnonymousNotifiable implements ClassMapSerializable
{
    public static function fromSerializedPayload(array $payload): ClassMapSerializable
    {
        $notifiable = new self();
        $notifiable->routes = $payload['routes'];

        return $notifiable;
    }

    public function toSerializedPayload(): array
    {
        return [
            'routes' => $this->routes,
        ];
    }
}
