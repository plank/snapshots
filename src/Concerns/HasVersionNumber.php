<?php

namespace Plank\Snapshots\Concerns;

use Plank\Snapshots\Casts\AsVersionNumber;
use Plank\Snapshots\Models\Version;

/**
 * @mixin Version
 */
trait HasVersionNumber
{
    public function initializeHasVersionNumber(): void
    {
        if (! $this->hasCast(static::keyColumn())) {
            $this->mergeCasts([
                static::keyColumn() => AsVersionNumber::class,
            ]);
        }
    }
}