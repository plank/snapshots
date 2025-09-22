<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Plank\Snapshots\Facades\Versions;
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
        if ($observer = config()->get('snapshots.observers.existense')) {
            static::observe($observer);
        }
    }

    public function existenses(): MorphMany
    {
        return $this->morphMany(config()->get('snapshots.models.existence'), 'trackable');
    }

    public function existense(): MorphOne
    {
        return $this->morphOne(config()->get('snapshots.models.existence'), 'trackable')
            ->where('version_id', Versions::active()?->getKey());
    }
}
