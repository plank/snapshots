<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Versioned;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithVersionedContent
{
    protected ManagesVersions $versions;

    public function initializeInteractsWithVersionedContent()
    {
        $this->versions = $this->getVersionRepository();
    }

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
        if (($this instanceof Versioned || $query->getModel() instanceof Versioned) && $active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
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
        if (($this instanceof Versioned || $query->getModel() instanceof Versioned) && $active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
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

    /**
     * Resolve the version repository instance.
     */
    public function getVersionRepository(): ManagesVersions
    {
        return app(ManagesVersions::class);
    }
}
