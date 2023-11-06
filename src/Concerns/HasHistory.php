<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Models\History;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<History> $history
 * @property-read bool $isHidden
 */
trait HasHistory
{
    public static function bootHasHistory(): void
    {
        if ($observer = config('snapshots.history.observer')) {
            static::observe($observer);
        }
    }

    public function history(): MorphMany
    {
        return $this->morphMany(config('snapshots.models.history'), 'trackable');
    }

    public function isHidden(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! in_array(SoftDeletes::class, class_uses_recursive($this))) {
                    return false;
                }

                return $this->trashed();
            }
        )->shouldCache();
    }
}
