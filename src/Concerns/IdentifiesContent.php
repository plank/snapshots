<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Identifying;

/**
 * @mixin Model
 * @mixin Identifying
 *
 * @property array $identifiesRelationships
 */
trait IdentifiesContent
{
    use HasIdentifyingRelationships;

    public static function bootIdentifiesContent(): void
    {
        // Identification requires history to be enabled
        if (! config()->get('snapshots.observers.history', false)) {
            return;
        }

        if ($observer = config()->get('snapshots.observers.identity')) {
            static::observe($observer);
        }
    }

    public function updateRelatedHashes(): void
    {
        static::identifiesRelationships()
            ->each(fn (string $relationship) => $this->updateRelationshipHashes($relationship));
    }

    protected function updateRelationshipHashes(string $relationship): void
    {
        Collection::wrap($this->$relationship)
            ->filter(fn (Model $model) => $model instanceof Identifiable)
            ->each(fn (Model&Identifiable $model) => $model->updateHash());
    }

    public static function identifiesRelationships(): Collection
    {
        return Collection::wrap(static::$identifiesRelationships ?? [])
            ->sort()
            ->values();
    }
}
