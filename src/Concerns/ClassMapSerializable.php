<?php

namespace Thomasjohnkane\Snooze\Concerns;

interface ClassMapSerializable
{
    public static function fromSerializedPayload(array $payload): self;

    public function toSerializedPayload(): array;
}
