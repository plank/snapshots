<?php

namespace Plank\Snapshots\Repository;

use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Models\Version as VersionModel;

class VersionRepository implements ManagesVersions
{
    protected ?Version $active = null;

    /**
     * {@inheritDoc}
     *
     * @param  VersionModel  $version
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
     *
     * @return VersionModel|null
     */
    public function active(): ?Version
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     *
     * @return VersionModel|null
     */
    public function latest(): ?Version
    {
        return VersionModel::query()
            ->latest()
            ->first();
    }

    /**
     * {@inheritDoc}
     *
     * @return VersionModel|null
     */
    public function find($key): ?Version
    {
        return VersionModel::find($key);
    }

    /**
     * {@inheritDoc}
     *
     * @return VersionModel|null
     */
    public function byNumber(string $number): ?Version
    {
        return VersionModel::query()
            ->where('number', $number)
            ->first();
    }

    /**
     * {@inheritDoc}
     *
     * @return Collection<VersionModel>
     */
    public function all(): Collection
    {
        return VersionModel::all();
    }
}
