<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Schema\Blueprint;

class SnapshotBlueprint extends Blueprint
{
    /**
     * Specify a foreign key for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
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
}
