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

    protected ?CausesChanges $causer = null;

    public function __construct(
        ManagesVersions $versions,
        ResolvesCauser $causers
    ) {
        if (is_a(static::class, Versioned::class, true)) {
            $this->active = $versions->active();
        }

        $this->causer = $causers->active();
    }

    public function created(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->{$model->getDeletedAtColumn()} !== null) {
            $this->deleted($model);

            return;
        }

        History::create([
            'operation' => Operation::Created,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->active?->getKey(),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => null,
            'to' => $this->getLoggableAttributes($model),
        ]);
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

        History::create([
            'operation' => Operation::Updated,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->active?->getKey(),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $this->getLoggableAttributes($model),
        ]);
    }

    public function deleted(Model&Trackable $model)
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

        if ($softDeletes && $model->isForceDeleting()) {
            return;
        }

        History::create([
            'operation' => $softDeletes ? Operation::SoftDeleted : Operation::Deleted,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->active?->getKey(),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $softDeletes ? $this->getLoggableAttributes($model) : null,
        ]);
    }

    public function restored(Model&Trackable $model)
    {
        History::create([
            'operation' => Operation::Restored,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->active?->getKey(),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => $this->getLoggableAttributes($model),
        ]);
    }

    public function forceDeleted(Model&Trackable $model)
    {
        History::create([
            'operation' => Operation::Deleted,
            'causer_id' => $this->causer?->getKey(),
            'causer_type' => $this->causer?->getMorphClass(),
            'version_id' => $this->active?->getKey(),
            'trackable_id' => $model->getKey(),
            'trackable_type' => $model->getMorphClass(),
            'from' => $this->getLoggableOriginal($model),
            'to' => null,
        ]);
    }

    protected function getLoggableAttributes(Model&Trackable $model): array
    {
        return $this->getLoggableArray(
            $model->getAttributes(),
            $model->getHidden()
        );
    }

    protected function getLoggableOriginal(Model&Trackable $model): array
    {
        return $this->getLoggableArray(
            $model->getOriginal(),
            $model->getHidden()
        );
    }

    protected function getLoggableArray(array $attributes, array $hidden): array
    {
        foreach ($hidden as $attribute) {
            unset($attributes[$attribute]);
        }

        return $attributes;
    }
}
