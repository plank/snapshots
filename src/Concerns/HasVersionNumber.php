<?php

namespace Plank\Snapshots\Concerns;

use Plank\Snapshots\Casts\AsVersionNumber;
use Plank\Snapshots\Models\Snapshot;

/**
 * @mixin Snapshot
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
