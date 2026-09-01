<?php

namespace Plank\Snapshots\Migrator\Blueprint;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;

class PlainForeignIdColumnDefinition extends ForeignIdColumnDefinition
{
    /**
     * The schema builder blueprint instance.
     *
     * @var Blueprint&SnapshotBlueprint
     */
    protected $blueprint;

    public function __construct(SnapshotBlueprint $blueprint, $attributes = [])
    {
        parent::__construct($blueprint, $attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function references($column, $indexName = null): ForeignKeyDefinition
    {
        return $this->blueprint->plainForeign($this->name, $indexName)->references($column);
    }
}
