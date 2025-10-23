<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\Existence;

class ExistenceObserver
{
    public function created(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->{$model->getDeletedAtColumn()} !== null) {
            $this->deleted($model);

            return;
        }

        /** @var class-string<Existence> $class */
        $class = config()->get('snapshots.models.existence');

        $model->setRelation('existence', $class::createOrUpdateFor($model, Versions::active()));
    }

    public function updated(Model&Trackable $model)
    {
        if (! $model instanceof Identifiable) {
            return;
        }

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

        $model->updateHash();
    }

    public function deleted(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->isForceDeleting()) {
            return;
        }

        $model->existence()->delete();
        $model->unsetRelation('existence');
    }

    public function restored(Model&Trackable $model)
    {
        if (! $model instanceof Identifiable) {
            return;
        }

        /** @var class-string<Existence> $class */
        $class = config()->get('snapshots.models.existence');

        $model->setRelation('existence', $class::createOrUpdateFor($model, Versions::active()));
    }

    public function forceDeleted(Model&Trackable $model)
    {
        $model->existence()->delete();
        $model->unsetRelation('existence');
    }
}
