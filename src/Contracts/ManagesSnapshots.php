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
     * Set the active Snapshot which will serve to scope queries to the correct table
     * 
     * Returns the Snapshot which was active prior to the Snapshot being passed in.
     */
    public function setActive(string|null|VersionKey|Snapshot $snapshot): ?Snapshot;

    /**
     * Clear the active Snapshot
     */
    public function clearActive(): void;

    /**
     * Retrieve the Snapshot which queries are having their tables prefixed with
     */
    public function active(): ?Snapshot;

    /**
     * Retrieve the latest Snapshot
     */
    public function latest(): ?Snapshot;

    /**
     * Retrieve the "working" Snapshot for a given Snapshot.
     */
    public function working((Snapshot&Model)|null $snapshot): ?Snapshot;

    /**
     * @template TReturn
     *
     * @param callable(?Snapshot $snapshot = null): TReturn $callback
     * @return TReturn
     */
    public function withActiveSnapshot(string|null|VersionKey|Snapshot $snapshot, Closure $callback): mixed;

    /**
     * Find a Snapshot by its key
     *
     * @param  string|int  $key
     */
    public function find($key): ?Snapshot;

    /**
     * Find a Snapshot by its uri key
     */
    public function byKey(string $key): ?Snapshot;

    /**
     * Retrieve all Snapshots
     *
     * @return Collection<Snapshot>
     */
    public function all(): Collection;
}
