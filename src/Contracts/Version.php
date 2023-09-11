<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property version|null $previous
 */
interface Version
{
    /**
     * Determine if the underlying table has been migrated already.
     */
    public function hasBeenMigrated(): bool;

    /**
     * Add a prefix to the migration name which is stored in the repository
     * when the migration is run.
     */
    public function addMigrationPrefix(string $name): string;

    /**
     * Remove the prefix from a migration name.
     */
    public function stripMigrationPrefix(string $name): string;

    /**
     * Resolve a Version from a migration name.
     */
    public function resolveVersionFromMigrationName(string $name): ?self;

    /**
     * Add a prefix for to the table names specified in SnapshotMigrations, and
     * also the Eloquent Models getTable() methods.
     */
    public function addTablePrefix(string $table): string;

    /**
     * Remove the prefix from a table name.
     */
    public function stripTablePrefix(string $table): string;

    /**
     * Determine if the Version has already been migrated.
     */
    public function isMigrated(): bool;

    /**
     * Get a string which can identify the version in a URL
     */
    public function uriKey(): string;

    /**
     * Get the version's identifying key
     *
     * @return int|string
     */
    public function getKey();

    /**
     * Retrieve the previous version if one is set
     *
     * @return BelongsTo|null
     */
    public function previous();
}
