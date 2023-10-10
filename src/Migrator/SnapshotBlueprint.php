<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Schema\Blueprint;

class SnapshotBlueprint extends Blueprint
{
    /**
     * {@inheritDoc}
     * @return SnapshotForeignKeyDefinition
     */
    public function foreign($columns, $name = null)
    {
        $command = new SnapshotForeignKeyDefinition(
            $this->indexCommand('foreign', $columns, $name)->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }

    /**
     * {@inheritDoc}
     * @return SnapshotForeignIdColumnDefinition
     */
    public function foreignId($column)
    {
        return $this->addColumnDefinition(new SnapshotForeignIdColumnDefinition($this, [
            'type' => 'bigInteger',
            'name' => $column,
            'autoIncrement' => false,
            'unsigned' => true,
        ]));
    }

    /**
     * {@inheritDoc}
     * @return SnapshotForeignIdColumnDefinition
     */
    public function foreignUuid($column)
    {
        return $this->addColumnDefinition(new SnapshotForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
    }

    /**
     * {@inheritDoc}
     * @return SnapshotForeignIdColumnDefinition
     */
    public function foreignUlid($column, $length = 26)
    {
        return $this->addColumnDefinition(new SnapshotForeignIdColumnDefinition($this, [
            'type' => 'char',
            'name' => $column,
            'length' => $length,
        ]));
    }
}
