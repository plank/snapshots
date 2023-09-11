<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\Version;

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
    public function addMigrationPrefix(string $name): string
    {
        return 'v'.$this->number->snake().'_'.$name;
    }

    /**
     * {@inheritDoc}
     */
    public function stripMigrationPrefix(string $name): string
    {
        return str($name)->replaceMatches('/^v\d+_\d+_\d+_/', '')->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function resolveVersionFromMigrationName(string $name): ?Version
    {
        if ($prefix = str($name)->match('/^v\d+_\d+_\d+/', '')) {
            $number = str($prefix)->replace('v', '')->replace('_', '.')->__toString();

            return $this->newQuery()->where('number', $number)->first();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function addTablePrefix(string $table): string
    {
        return 'v'.$this->number->snake().'_'.$this->stripTablePrefix($table);
    }

    /**
     * {@inheritDoc}
     */
    public function stripTablePrefix(string $table): string
    {
        return str($table)->replaceMatches('/^v\d+_\d+_\d+_/', '')->__toString();
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
