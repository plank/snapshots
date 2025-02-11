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
     * Determine if the Version has already been migrated.
     */
    public function isMigrated(): bool;

    /**
     * Get the version's identifying key
     */
    public static function keyColumn(): string;

    /**
     * Get a string which can identify the version in a URL
     */
    public function key(): VersionKey;

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
