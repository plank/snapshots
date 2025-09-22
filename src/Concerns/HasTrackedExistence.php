<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Plank\Snapshots\Facades\Snapshots;
use Plank\Snapshots\Models\Existence;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<Existence> $existences
 * @property-read ?Existence $existence
 */
trait HasTrackedExistence
{
    use IdentifiedContent;

    public static function bootHasTrackedExistence(): void
    {
        if ($observer = config()->get('snapshots.observers.existence')) {
            static::observe($observer);
        }
    }

    public function existences(): MorphMany
    {
        return $this->morphMany(config()->get('snapshots.models.existence'), 'trackable');
    }

    public function existence(): MorphOne
    {
        return $this->morphOne(config()->get('snapshots.models.existence'), 'trackable')
            ->where('snapshot_id', Snapshots::active()?->getKey());
    }
}
