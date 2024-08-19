<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Identifiable;

/**
 * @mixin BelongsToMany
 */
trait AsIdentifyingRelationship
{
    public function attach($id, array $attributes = [], $touch = true)
    {
        parent::attach($id, $attributes, $touch);

        $this->updateIdentities($id);
    }

    public function detach($ids = null, $touch = true)
    {
        parent::detach($ids, $touch);

        $this->updateIdentities($ids);
    }

    protected function updateIdentities($ids)
    {
        if ($this->parent instanceof Identifiable) {
            $this->parent->updateHash();
        }

        Collection::wrap($ids)
            ->map(function ($id) {
                return $id instanceof Model
                    ? $id
                    : $this->related->query()->whereKey($id)->first();
            })
            ->filter(fn (Model $model) => $model instanceof Identifiable)
            ->each(fn (Model&Identifiable $model) => $model->updateHash());
    }
}
