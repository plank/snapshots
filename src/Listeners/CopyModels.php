<?php

namespace Plank\Snapshots\Listeners;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\TableCreated;

class CopyModels
{
    public function __construct(
        public ManagesVersions $versions
    ) {
    }

    public function handle(TableCreated $event)
    {
        if ($event->model === null) {
            // In the event a model isn't provided, like for pivot tables we will need to fall back 
            // to the CopyTable behavior.
            $tableCopier = new CopyTable($this->versions);
            $tableCopier->handle($event);

            return;
        }

        if ($event->version === null) {
            return;
        }

        $active = $this->versions->active();

        $this->versions->clearActive();

        /** @var Model&Versioned $model */
        $model = new ($event->model);
        $models = $model->newQueryWithoutScopes()->cursor();
        $this->versions->setActive($event->version);

        $this->quietlyReplicate($event->model, function () use ($models) {
            $models->each(function (Model&Versioned $model) {
                $id = $model->getKeyName();
                $replicated = $model->replicate();
                $replicated->$id = $model->$id;
                $replicated->setCreatedAt($model->{$model->getCreatedAtColumn()});
                $replicated->setUpdatedAt($model->{$model->getUpdatedAtColumn()});
                $replicated->save();
            });
        });

        $this->versions->setActive($active);

        Event::dispatch(TableCopied::fromCreated($event));
    }

    /**
     * @var class-string<Model&Versioned>
     */
    protected function quietlyReplicate(string $model, Closure $callback)
    {
        Schema::disableForeignKeyConstraints();

        $model::withoutTimestamps(function () use ($model, $callback) {
            $historyEnabled = config('snapshots.history');
            $hushed = in_array(HushesHandlers::class, class_uses_recursive($model));

            if ($historyEnabled && $hushed) {
                $model::withoutObserver(config('snapshots.history.observer'), $callback);
            } else {
                $callback();
            }
        });

        Schema::enableForeignKeyConstraints();
    }
}
