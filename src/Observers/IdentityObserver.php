<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Contracts\Identifying;

class IdentityObserver
{
    public function saved(Model&Identifying $model)
    {
        $model->updateRelatedHashes();
    }

    public function deleting(Model&Identifying $model)
    {
        $model->load($model::identifiesRelationships()->toArray());
    }

    public function deleted(Model&Identifying $model)
    {
        $model->updateRelatedHashes();
    }

    public function restored(Model&Identifying $model)
    {
        $model->updateRelatedHashes();
    }

    public function forceDeleted(Model&Identifying $model)
    {
        $model->updateRelatedHashes();
    }
}
