<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AsSnapshot
{
    /**
     * {@inheritDoc}
     */
    public function hasBeenMigrated(): bool
    {
        return Schema::hasTable($this->getTable());
    }

    /**
     * {@inheritDoc}
     */
    public function isMigrated(): bool
    {
        return (bool) $this->migrated;
    }

    /**
     * {@inheritDoc}
     */
    public function previous()
    {
        return $this->belongsTo(static::class, 'previous_snapshot_id');
    }
}
