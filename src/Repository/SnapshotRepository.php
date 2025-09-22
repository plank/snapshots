<?php

namespace Plank\Snapshots\Repository;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesSnapshots;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Models\Snapshot as SnapshotModel;

class SnapshotRepository implements ManagesSnapshots
{
    protected (Snapshot&Model)|null $active = null;

    /**
     * Get an instance of the Model being used for versions.
     */
    public function model(): Snapshot&Model
    {
        return new (config()->get('snapshots.models.snapshot') ?? SnapshotModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(string|null|VersionKey|Snapshot $snapshot): ?Snapshot
    {
        $oldActive = $this->active;

        if ($snapshot && ! $snapshot instanceof Snapshot) {
            $snapshot = $this->byKey($snapshot);
        }

        $this->active = $snapshot;

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
    public function active(): (Snapshot&Model)|null
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     */
    public function latest(): (Snapshot&Model)|null
    {
        return $this->model()
            ->query()
            ->latest()
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function working((Snapshot&Model)|null $snapshot): ?Snapshot
    {
        return null;
    }

    /**
     * @template TReturn
     *
     * @param callable((Snapshot&Model)|null $snapshot = null): TReturn $callback
     * @return TReturn
     */
    public function withActiveSnapshot(string|null|VersionKey|Snapshot $snapshot, Closure $callback): mixed
    {
        $oldActive = $this->setActive($snapshot);

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
    public function find($key): (Snapshot&Model)|null
    {
        return $this->model()
            ->query()
            ->whereKey($key)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function byKey(string|VersionKey $key): (Snapshot&Model)|null
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
