<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Models\History;

class HistoryObserver
{
    protected ?Version $active = null;

    protected ?ManagesVersions $versions = null;

    protected ?CausesChanges $causer = null;

    public function __construct(
        ManagesVersions $versions,
        ResolvesCauser $causers
    ) {
        $this->versions = $versions;
        $this->causer = $causers->active();
    }

    public function created(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->{$model->getDeletedAtColumn()} !== null) {
            $this->deleted($model);

            return;
        }

        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => Operation::Created,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => null,
            'to' => $this->getLoggableAttributes($model),
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = $model->newHash();
        }

        $history::create($data);
    }

    public function updated(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes) {
            if ($model->{$model->getDeletedAtColumn()} !== null) {
                $this->deleted($model);

                return;
            }

            if ($model->isDirty($model->getDeletedAtColumn())) {
                // This will still be handled in the "restored" handler
                return;
            }
        }

        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => Operation::Updated,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $this->getLoggableAttributes($model),
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = $model->newHash();
        }

        $history::create($data);
    }

    public function deleted(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->isForceDeleting()) {
            return;
        }

        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => $softDeletes ? Operation::SoftDeleted : Operation::Deleted,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $softDeletes ? $this->getLoggableAttributes($model) : null,
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = $softDeletes ? $model->newHash() : null;
        }

        $history::create($data);
    }

    public function restored(Model&Trackable $model)
    {
        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => Operation::Restored,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $this->getLoggableAttributes($model),
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = $model->newHash();
        }

        $history::create($data);
    }

    public function forceDeleted(Model&Trackable $model)
    {
        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => Operation::Deleted,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => null,
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = null;
        }

        $history::create($data);
    }

    protected function activeVersionId(Model&Trackable $model): int|string|null
    {
        if ($model instanceof Versioned) {
            return $this->versions->active()?->getKey();
        }

        return null;
    }

    protected function getLoggableAttributes(Model&Trackable $model): array
    {
        return $this->getLoggableArray(
            $model->getAttributes(),
            $model->getHidden(),
            $model->getVisible()
        );
    }

    protected function getLoggableOriginal(Model&Trackable $model): array
    {
        return $this->getLoggableArray(
            $model->getOriginal(),
            $model->getHidden(),
            $model->getVisible()
        );
    }

    protected function getLoggableArray(array $attributes, array $hidden, array $visible): array
    {
        if (count($visible) > 0) {
            $attributes = array_intersect_key($attributes, array_flip($visible));
        }

        if (count($hidden) > 0) {
            $attributes = array_diff_key($attributes, array_flip($hidden));
        }

        return $attributes;
    }
}
