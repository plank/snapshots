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
        return $this->number->key().'_'.$name;
    }

    /**
     * {@inheritDoc}
     */
    public static function stripMigrationPrefix(string $name): string
    {
        return (string) str($name)->replaceMatches('/^v\d+_\d+_\d+_/', '');
    }

    /**
     * {@inheritDoc}
     */
    public function resolveVersionFromMigrationName(string $name): ?Version
    {
        $prefix = str($name)->match('/^v\d+_\d+_\d+/', '');

        if ($prefix->isEmpty()) {
            return null;
        }

        $number = (string) $prefix->replace('v', '')->replace('_', '.');

        return $this->newQuery()->where('number', $number)->first();
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
    public function stripTablePrefix(string $table): string
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
