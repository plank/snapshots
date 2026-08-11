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
    /**
     * Pivot columns that participate in the related models' identity.
     *
     * @var array<int, string>
     */
    protected array $identifyingPivot = [];

    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  bool  $touch
     * @return void
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        parent::attach($id, $attributes, $touch);

        $this->updateIdentities($id);
    }

    /**
     * Detach models from the relationship.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($ids = null, $touch = true)
    {
        $result = parent::detach($ids, $touch);

        $this->updateIdentities($ids);

        return $result;
    }

    /**
     * Update an existing pivot record on the table.
     *
     * @param  mixed  $id
     * @param  bool  $touch
     * @return int
     */
    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        $result = parent::updateExistingPivot($id, $attributes, $touch);

        $this->updateIdentities($id);

        return $result;
    }

    /**
     * Declare which pivot columns participate in the related models' identity.
     *
     * @param  array<int, string>  $columns
     */
    public function withIdentifyingPivot(array $columns): static
    {
        $this->identifyingPivot = array_values($columns);

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function identifyingPivotColumns(): array
    {
        return $this->identifyingPivot;
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
