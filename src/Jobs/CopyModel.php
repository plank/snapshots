<?php

namespace Plank\Snapshots\Jobs;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\LaravelModelResolver\Facades\Models;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Facades\Versions;

class CopyModel extends Copier
{
    public function handle()
    {
        $model = Models::fromTable($this->table);

        if ($model === null || ! is_a($model, Versioned::class, true)) {
            $this->batch()->add(new CopyTable($this->version, $this->table));

            return;
        }

        // Store the active version so we can restore it when we are done our work
        $active = Versions::active();

        // Grab the data from the "working version"
        $working = Versions::working($this->version);
        Versions::setActive($working);
        $models = $model::query()->withoutGlobalScopes()->cursor();

        // Set the version to the newly created table's version, so we can begin copying
        // over the data.
        try {
            Versions::setActive($this->version);

            $this->quietlyReplicate($model, function () use ($models) {
                $models->each(function (Model&Versioned $model) {
                    $id = $model->getKeyName();
                    $replicated = $model->replicate();
                    $replicated->$id = $model->$id;
                    $replicated->setCreatedAt($model->{$model->getCreatedAtColumn()});
                    $replicated->setUpdatedAt($model->{$model->getUpdatedAtColumn()});
                    $replicated->save();
                });
            });
        } finally {
            Versions::setActive($active);
        }

        if (config('snapshots.observers.history')) {
            $this->writeHistory($model);
        }
    }

    /**
     * @var class-string<Model&Versioned>
     */
    protected function quietlyReplicate(string $model, Closure $callback): void
    {
        Schema::withoutForeignKeyConstraints(function () use ($model, $callback) {
            $model::withoutTimestamps(function () use ($model, $callback) {
                $observer = config()->get('snapshots.observers.history');
                $hushed = in_array(HushesHandlers::class, class_uses_recursive($model));

                if ($observer && $hushed) {
                    $model::withoutObserver($observer, $callback);
                } else {
                    $callback();
                }
            });
        });
    }
}
