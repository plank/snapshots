<?php

namespace Plank\Snapshots\Repository;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Models\Version as VersionModel;

class VersionRepository implements ManagesVersions
{
    protected ?Version $active = null;

    /**
     * Get an instance of the Model being used for versions.
     */
    public function model(): Version&Model
    {
        return new (config()->get('snapshots.models.version') ?? VersionModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(string|null|VersionKey|Version $version): ?Version
    {
        $oldActive = $this->active;

        if ($version && ! $version instanceof Version) {
            $version = $this->byKey($version);
        }

        $this->active = $version;

        return $oldActive;
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
    public function active(): Version|null
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     */
    public function latest(): Version|null
    {
        return $this->model()
            ->query()
            ->latest()
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function working(?Version $version): ?Version
    {
        return null;
    }

    /**
     * @template TReturn
     *
     * @param callable(?Version $version = null): TReturn $callback
     * @return TReturn
     */
    public function withVersionActive(string|null|VersionKey|Version $version, Closure $callback): mixed
    {
        $oldActive = $this->setActive($version);

        try {
            $result = $callback($this->active);
        } finally {
            $this->setActive($oldActive);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function find($key): Version|null
    {
        return $this->model()
            ->query()
            ->whereKey($key)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function byKey(string|VersionKey $key): Version|null
    {
        $model = $this->model();

        if (is_string($key)) {
            /** @var class-string<VersionKey> $keyClass */
            $keyClass = config('snapshots.value_objects.version_key');

            try {
                $key = $keyClass::fromString($key);
            } catch (\InvalidArgumentException) {
                return null;
            }
        }

        return $model->query()
            ->where($model::keyColumn(), $key)
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
