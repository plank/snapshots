<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Schema\Blueprint;
use Plank\Snapshots\Facades\Versions;

class SnapshotBlueprint extends Blueprint
{
    /**
     * {@inheritDoc}
     *
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
     *
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
     *
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
     *
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

    /**
     * {@inheritDoc}
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        $columns = (array) $columns;

        // If no name was specified for this index, we will create one using a basic
        // convention of the table name, followed by the columns, followed by an
        // index type, such as primary or index, which makes the index unique.
        $index = $index ?: $this->createIndexName($type, $columns);

        if ($version = Versions::active()) {
            $index = $version->key()->prefix($index);
        }

        return $this->addCommand(
            $type, compact('index', 'columns', 'algorithm')
        );
    }
}
