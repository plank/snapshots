<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Facades\Causer;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\History;

class HistoryObserver
{
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
            'causer_id' => Causer::active()?->getKey(),
            'causer_type' => Causer::active()?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => null,
            'to' => $model->trackableAttributes(),
        ];

        if (config()->get('snapshots.observers.identity')) {
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
            'causer_id' => Causer::active()?->getKey(),
            'causer_type' => Causer::active()?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $model->trackableOriginal(),
            'to' => $model->trackableAttributes(),
        ];

        if (config()->get('snapshots.observers.identity')) {
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
            'causer_id' => Causer::active()?->getKey(),
            'causer_type' => Causer::active()?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $model->trackableOriginal(),
            'to' => $softDeletes ? $model->trackableAttributes() : null,
        ];

        if (config()->get('snapshots.observers.identity')) {
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
            'causer_id' => Causer::active()?->getKey(),
            'causer_type' => Causer::active()?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $model->trackableOriginal(),
            'to' => $model->trackableAttributes(),
        ];

        if (config()->get('snapshots.observers.identity')) {
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
            'causer_id' => Causer::active()?->getKey(),
            'causer_type' => Causer::active()?->getMorphClass(),
            'version_id' => $this->activeVersionId($model),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $model->trackableOriginal(),
            'to' => null,
        ];

        if (config()->get('snapshots.observers.identity')) {
            $data['hash'] = null;
        }

        $history::create($data);
    }

    protected function activeVersionId(Model&Trackable $model): int|string|null
    {
        if ($model instanceof Versioned) {
            return Versions::active()?->getKey();
        }

        return null;
    }
}
