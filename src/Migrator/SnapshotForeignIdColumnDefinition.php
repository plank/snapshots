<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Support\Str;

class SnapshotForeignIdColumnDefinition extends ForeignIdColumnDefinition
{
    /**
     * The schema builder blueprint instance.
     *
     * @var SnapshotBlueprint
     */
    protected $blueprint;

    public function __construct(SnapshotBlueprint $blueprint, $attributes = [])
    {
        parent::__construct($blueprint, $attributes);
    }

    /**
     * {@inheritDoc}
     * @return SnapshotForeignKeyDefinition
     */
    public function constrainedToSnapshot($table = null, $column = 'id', $indexName = null)
    {
        return $this->references($column, $indexName)->onSnapshot($table ?? Str::of($this->name)->beforeLast('_'.$column)->plural());
    }

    /**
     * {@inheritDoc}
     * @return SnapshotForeignKeyDefinition
     */
    public function references($column, $indexName = null): SnapshotForeignKeyDefinition
    {
        return $this->blueprint->foreign($this->name, $indexName)->references($column);
    }
}
