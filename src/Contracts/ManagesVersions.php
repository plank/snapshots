<?php

namespace Plank\Snapshots\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ManagesVersions
{
    /**
     * Get an instance of the Model being used for versions.
     */
    public function model(): Version&Model;

    /**
     * Set the active version which will serve to scope queries to the correct table
     * and return the previously active Version
     */
    public function setActive(string|null|VersionKey|Version $version): ?Version;

    /**
     * Clear the active version
     */
    public function clearActive(): void;

    /**
     * Retrieve the version which queries are having their tables prefixed with
     */
    public function active(): ?Version;

    /**
     * @template TReturn
     *
     * @param callable(?Version $version = null): TReturn $callback
     * @return TReturn
     */
    public function withVersionActive(string|null|VersionKey|Version $version, Closure $callback): mixed;

    /**
     * Retrieve the latest version
     */
    public function latest(): ?Version;

    /**
     * Find a version by its key
     *
     * @param  string|int  $key
     */
    public function find($key): ?Version;

    /**
     * Find a version by its uri key
     */
    public function byKey(string $key): ?Version;

    /**
     * Retrieve all versions
     *
     * @return Collection<Version>
     */
    public function all(): Collection;
}
