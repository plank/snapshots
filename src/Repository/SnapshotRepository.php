<?php

namespace Plank\Snapshots\Repository;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesSnapshots;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Contracts\SnapshotKey;
use Plank\Snapshots\Models\Snapshot as SnapshotModel;

class SnapshotRepository implements ManagesSnapshots
{
    protected ?Snapshot $active = null;

    /**
     * Get an instance of the Model being used for snapshots.
     */
    public function model(): Snapshot&Model
    {
        return new (config()->get('snapshots.models.snapshot') ?? SnapshotModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(string|null|SnapshotKey|Snapshot $snapshot): ?Snapshot
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
    public function working(?Snapshot $snapshot): ?Snapshot
    {
        return null;
    }

    /**
     * @template TReturn
     *
     * @param callable(?Snapshot $snapshot = null): TReturn $callback
     * @return TReturn
     */
    public function withSnapshotActive(string|null|SnapshotKey|Snapshot $snapshot, Closure $callback): mixed
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
    public function byKey(string|SnapshotKey $key): (Snapshot&Model)|null
    {
        $model = $this->model();

        if (is_string($key)) {
            /** @var class-string<SnapshotKey> $keyClass */
            $keyClass = config('snapshots.value_objects.snapshot_key');

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
