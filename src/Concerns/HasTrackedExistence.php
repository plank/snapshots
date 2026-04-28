<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\Existence;

/**
 * @mixin Model
 *
 * @property-read Collection<Existence> $existences
 * @property-read ?Existence $existence
 */
trait HasTrackedExistence
{
    use IdentifiedContent;

    public static function bootHasTrackedExistence(): void
    {
        $observerClass = config()->get('snapshots.observers.existence');

        if (! $observerClass) {
            return;
        }

        $observer = app($observerClass);

        foreach (get_class_methods($observer) as $method) {
            if (! str_starts_with($method, '__')) {
                static::registerModelEvent($method, [$observer, $method]);
            }
        }
    }

    public function existences(): MorphMany
    {
        return $this->morphMany(config()->get('snapshots.models.existence'), 'trackable');
    }

    public function existence(): MorphOne
    {
        /** @var class-string<Existence> $class */
        $class = config()->get('snapshots.models.existence');

        return $this->morphOne(config()->get('snapshots.models.existence'), 'trackable')
            ->where($class::versionColumn(), Versions::active()?->getKey());
    }
}
