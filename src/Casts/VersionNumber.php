<?php

namespace Plank\Snapshots\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Plank\Snapshots\ValueObjects\VersionNumber as VersionNumberValueObject;

class VersionNumber implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return VersionNumberValueObject::fromVersionString($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            $value = VersionNumberValueObject::fromVersionString($value);
        }

        if (! $value instanceof VersionNumberValueObject) {
            throw new \InvalidArgumentException('The given value is not a VersionNumber instance.');
        }

        return (string) $value;
    }
}
