<?php

namespace Plank\Snapshots\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Models\Version as VersionModel;

class VersionRepository implements ManagesVersions
{
    protected ?Version $active = null;

    /**
     * Get an instance of the Model being used for versions.
     */
    public function model(): Version&Model
    {
        return new (config('snapshots.model') ?? VersionModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(?Version $version): void
    {
        $this->active = $version;
    }

    /**
     * {@inheritDoc}
     */
    public function clearActive(): void
    {
        $this->active = null;
    }

    /**
     * {@inheritDoc}
     */
    public function active(): (Version&Model)|null
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     */
    public function latest(): (Version&Model)|null
    {
        return $this->model()
            ->query()
            ->latest()
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function find($key): (Version&Model)|null
    {
        return $this->model()
            ->query()
            ->whereKey($key)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function byNumber(string $number): (Version&Model)|null
    {
        return $this->model()
            ->query()
            ->where('number', $number)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->model()->all();
    }
}
