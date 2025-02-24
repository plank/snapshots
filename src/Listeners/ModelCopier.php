<?php

namespace Plank\Snapshots\Listeners;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\LaravelModelResolver\Facades\Models;
use Plank\LaravelSchemaEvents\Events\TableCreated;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Events\TableCopied;

class ModelCopier
{
    public function __construct(
        protected ManagesVersions $versions
    ) {}

    public function handle(TableCreated $created)
    {
        Schema::disableForeignKeyConstraints();

        $version = $this->versions->byKey($created->table);

        if ($version === null) {
            return;
        }

        $model = Models::fromTable($created->table);

        if ($model === null) {
            return;
        }

        // Store the active version so we can restore it when we are done our work
        $active = $this->versions->active();

        // Grab the data from the "working version"
        $this->versions->clearActive();
        $models = $model::query()->withoutGlobalScopes()->cursor();

        // Set the version to the newly created table's version, so we can begin copying
        // over the data.
        $this->versions->setActive($version);

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

        $this->versions->setActive($active);

        Event::dispatch(new TableCopied($created->table));

        Schema::enableForeignKeyConstraints();
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
