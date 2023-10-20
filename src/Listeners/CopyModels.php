<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Exceptions\SchemaModelException;

class CopyModels
{
    public function __construct(
        public ManagesVersions $versions
    ) {
    }

    public function handle(TableCreated $event)
    {
        if ($event->model === null) {
            throw SchemaModelException::create($event->table);
        }

        if ($event->version === null) {
            return;
        }

        $this->versions->clearActive();

        /** @var Model&Versioned $model */
        $model = new ($event->model);
        $models = $model->newQueryWithoutScopes()->cursor();
        $this->versions->setActive($event->version);

        Schema::withoutForeignKeyConstraints(function () use ($models) {
            $models->each(function (Model&Versioned $model) {
                $id = $model->getKeyName();
                $replicated = $model->replicate();
                $replicated->$id = $model->$id;
                $replicated->save();
            });
        });
    }
}
