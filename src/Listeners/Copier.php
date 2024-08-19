<?php

namespace Plank\Snapshots\Listeners;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\TableCreated;

class Copier
{
    public function __construct(
        protected ManagesVersions $versions,
        protected ManagesCreatedTables $tables
    ) {}

    public function handle()
    {
        $active = $this->versions->active();

        Schema::disableForeignKeyConstraints();

        while ($created = $this->tables->dequeue()) {
            if ($created->version === null) {
                continue;
            }

            if (config()->get('snapshots.copier.model_events') && $created->model) {
                $this->copyModel($created);
            } else {
                $this->copyTable($created);
            }

            Event::dispatch(TableCopied::fromCreated($created));
        }

        Schema::enableForeignKeyConstraints();

        $this->versions->setActive($active);
    }

    protected function copyModel(TableCreated $created): void
    {
        // Clear the active version so we can get a query for the model that is scoped
        // to the working version.
        $this->versions->clearActive();

        /** @var Model&Versioned $model */
        $model = new ($created->model);
        $models = $model->newQueryWithoutScopes()->cursor();

        // Set the version to the newly created table's version, so we can begin copying
        // over the data.
        $this->versions->setActive($created->version);

        $this->quietlyReplicate($created->model, function () use ($models) {
            $models->each(function (Model&Versioned $model) {
                $id = $model->getKeyName();
                $replicated = $model->replicate();
                $replicated->$id = $model->$id;
                $replicated->setCreatedAt($model->{$model->getCreatedAtColumn()});
                $replicated->setUpdatedAt($model->{$model->getUpdatedAtColumn()});
                $replicated->save();
            });
        });
    }

    protected function copyTable(TableCreated $created): void
    {
        $from = $created->table;
        $to = $created->version->addTablePrefix($from);

        Schema::withoutForeignKeyConstraints(function () use ($from, $to) {
            DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
        });
    }

    /**
     * @var class-string<Model&Versioned>
     */
    protected function quietlyReplicate(string $model, Closure $callback): void
    {
        Schema::disableForeignKeyConstraints();

        $model::withoutTimestamps(function () use ($model, $callback) {
            $observer = config()->get('snapshots.history.observer');
            $hushed = in_array(HushesHandlers::class, class_uses_recursive($model));

            if ($observer && $hushed) {
                $model::withoutObserver(config()->get('snapshots.history.observer'), $callback);
            } else {
                $callback();
            }
        });

        Schema::enableForeignKeyConstraints();
    }
}
