<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Facades\Snapshots;

class ExistenceObserver
{
    public function created(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->{$model->getDeletedAtColumn()} !== null) {
            $this->deleted($model);

            return;
        }

        $existence = $model->existence()->create([
            'snapshot_id' => Snapshots::active()?->getKey(),
            'hash' => $model instanceof Identifiable ? $model->newHash() : null,
        ]);

        $model->setRelation('existence', $existence);
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

        $existence = $model->existence()->create([
            'snapshot_id' => Snapshots::active()?->getKey(),
            'hash' => $model instanceof Identifiable ? $model->newHash() : null,
        ]);

        $model->setRelation('existence', $existence);
    }

    public function forceDeleted(Model&Trackable $model)
    {
        $model->existence()->delete();
        $model->unsetRelation('existence');
    }
}
