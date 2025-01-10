<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property \Plank\Snapshots\ValueObjects\VersionNumber $number
 */
trait AsVersion
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
    public function addTablePrefix(string $table): string
    {
        return $this->number->key().'_'.$this->stripTablePrefix($table);
    }

    /**
     * {@inheritDoc}
     */
    public static function stripTablePrefix(string $table): string
    {
        return (string) str($table)->replaceMatches('/^v\d+_\d+_\d+_/', '');
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
        return $this->belongsTo(static::class, 'previous_version_id');
    }
}
