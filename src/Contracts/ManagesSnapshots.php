<?php

namespace Plank\Snapshots\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ManagesSnapshots
{
    /**
     * Get an instance of the Model being used for snapshots.
     */
    public function model(): Snapshot&Model;

    /**
     * Set the active snapshot which will serve to scope queries to the correct table
     * and return the previously active Snapshot
     */
    public function setActive(string|null|SnapshotKey|Snapshot $snapshot): ?Snapshot;

    /**
     * Clear the active snapshot
     */
    public function clearActive(): void;

    /**
     * Retrieve the snapshot which queries are having their tables prefixed with
     */
    public function active(): ?Snapshot;

    /**
     * Retrieve the latest snapshot
     */
    public function latest(): ?Snapshot;

    /**
     * Retrieve the working snapshot for a given snapshot.
     */
    public function working(?Snapshot $snapshot): ?Snapshot;

    /**
     * @template TReturn
     *
     * @param callable(?Snapshot $snapshot = null): TReturn $callback
     * @return TReturn
     */
    public function withSnapshotActive(string|null|SnapshotKey|Snapshot $snapshot, Closure $callback): mixed;

    /**
     * Find a snapshot by its key
     *
     * @param  string|int  $key
     */
    public function find($key): ?Snapshot;

    /**
     * Find a snapshot by its uri key
     */
    public function byKey(string $key): ?Snapshot;

    /**
     * Retrieve all snapshots
     *
     * @return Collection<Snapshot>
     */
    public function all(): Collection;
}
