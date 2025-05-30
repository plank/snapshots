<?php

namespace Plank\Snapshots\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Plank\Snapshots\ValueObjects\VersionNumber as VersionNumberValueObject;

class AsVersionNumber implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return VersionNumberValueObject::fromString($value ?? '0.0.0');
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            $value = VersionNumberValueObject::fromString($value);
        }

        if (! $value instanceof VersionNumberValueObject) {
            throw new \InvalidArgumentException('The given value is not a VersionNumber instance.');
        }

        return (string) $value;
    }
}
