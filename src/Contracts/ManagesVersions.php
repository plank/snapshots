<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Support\Collection;

interface ManagesVersions
{
    /**
     * Set the active version which will serve to scope queries to the correct table
     */
    public function setActive(?Version $version): void;

    /**
     * Clear the active version
     */
    public function clearActive(): void;

    /**
     * Retrieve the version which queries are having their tables prefixed with
     */
    public function active(): ?Version;

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
     * Find a version by its number
     */
    public function byNumber(string $number): ?Version;

    /**
     * Retrieve all versions
     *
     * @return Collection<Version>
     */
    public function all(): Collection;
}
