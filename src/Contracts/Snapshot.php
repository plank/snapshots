<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property snapshot|null $previous
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
     * Get the snapshot's identifying key
     */
    public static function keyColumn(): string;

    /**
     * Get a string which can identify the snapshot in a URL
     */
    public function key(): SnapshotKey;

    /**
     * Get the snapshot's identifying key
     *
     * @return int|string
     */
    public function getKey();

    /**
     * Retrieve the previous snapshot if one is set
     *
     * @return BelongsTo|null
     */
    public function previous();
}
