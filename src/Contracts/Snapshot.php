<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Snapshot|null $previous
 */
interface Snapshot
{
    /**
     * Determine if the underlying table has been migrated already.
     */
    public function hasBeenMigrated(): bool;

    /**
     * Determine if the Snapshot has already been migrated.
     */
    public function isMigrated(): bool;

    /**
     * Get the Snapshot's identifying key
     */
    public static function keyColumn(): string;

    /**
     * Get a string which can identify the Snapshot in a URL
     */
    public function key(): VersionKey;

    /**
     * Get the Snapshot's identifying key
     *
     * @return int|string
     */
    public function getKey();

    /**
     * Retrieve the previous Snapshot if one exists
     *
     * @return BelongsTo|null
     */
    public function previous();
}
