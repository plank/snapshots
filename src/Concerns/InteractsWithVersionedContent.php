<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Identifying;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Facades\Versions;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithVersionedContent
{
    use HasIdentifyingRelationships;

    protected ManagesVersions $versions;

    protected function newBelongsToMany(
        Builder $query,
        Model $parent,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    ) {
        if (($this instanceof Versioned || $query->getModel() instanceof Versioned) && $active = Versions::active()) {
            $table = $active->addTablePrefix($table);
        }

        if ($this instanceof Identifying || $this instanceof Identifiable) {
            return $this->newIdentifyingBelongsToMany(
                $query,
                $parent,
                $table,
                $foreignPivotKey,
                $relatedPivotKey,
                $parentKey,
                $relatedKey,
                $relationName
            );
        }

        return parent::newBelongsToMany(
            $query,
            $parent,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function newMorphToMany(
        Builder $query,
        Model $parent,
        $name,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null,
        $inverse = false
    ) {
        if (($this instanceof Versioned || $query->getModel() instanceof Versioned) && $active = Versions::active()) {
            $table = $active->addTablePrefix($table);
        }

        if ($this instanceof Identifying || $this instanceof Identifiable) {
            return $this->newIdentifyingMorphToMany(
                $query,
                $parent,
                $name,
                $table,
                $foreignPivotKey,
                $relatedPivotKey,
                $parentKey,
                $relatedKey,
                $relationName,
                $inverse
            );
        }

        return parent::newMorphToMany(
            $query,
            $parent,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName,
            $inverse
        );
    }
}
