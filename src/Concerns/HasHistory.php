<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\History;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<History> $history
 * @property-read Collection<History> $visibleHistory
 * @property-read bool $hidden
 */
trait HasHistory
{
    use IdentifiedContent;

    public static function bootHasHistory(): void
    {
        if ($observer = config()->get('snapshots.observers.history')) {
            static::observe($observer);
        }
    }

    public function history(): MorphMany
    {
        return $this->morphMany(config()->get('snapshots.models.history'), 'trackable');
    }

    public function activeHistoryItem(): ?History
    {
        return $this->history()
            ->where('version_id', Versions::active()?->getKey())
            ->latest()
            ->first();
    }

    public function hidden(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! in_array(SoftDeletes::class, class_uses_recursive($this))) {
                    return ! $this->exists;
                }

                return $this->trashed();
            }
        );
    }

    public function visibleHistory(): Collection
    {
        return $this->history()
            ->with('version')
            ->get()
            ->groupBy('version_id')
            ->map(function (Collection $items) {
                // For visiblity we are only concerned with the most recent history item
                // from the version. Ie: We only want to see the last thing that happened
                // to that Model in that version.
                $item = $items->sortByDesc('created_at')->first();

                // Since we need the trackable model from the version the history item is in, we run
                // the query in that version so it is referencing the correct table.
                //
                // We also need to remove the global scopes since in this context, we want the models
                // regradless of any other criteria.
                $trackable = Versions::withVersionActive($item->version, function () use ($item) {
                    return $item->trackable()
                        ->withoutGlobalScopes()
                        ->first();
                });

                // We set the relation manually to the correct version is persisted on the item. If
                // we did not do this, and the trackable was resolved later, it would resort back
                // to whatever the active version is.
                $item->setRelation('trackable', $trackable);

                return $item;
            })
            ->reject(fn (History $item) => $item->trackable === null);
    }

    public function trackableAttributes(): array
    {
        return $this->trackableArray(
            $this->getAttributes(),
            $this->getHidden(),
            $this->getVisible(),
        );
    }

    public function trackableOriginal(): array
    {
        return $this->trackableArray(
            $this->getOriginal(),
            $this->getHidden(),
            $this->getVisible(),
        );
    }

    protected function trackableArray(array $attributes, array $hidden, array $visible): array
    {
        if (count($visible) > 0) {
            $attributes = array_intersect_key($attributes, array_flip($visible));
        }

        if (count($hidden) > 0) {
            $attributes = array_diff_key($attributes, array_flip($hidden));
        }

        return $attributes;
    }
}
