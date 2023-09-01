<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Schema\ForeignKeyDefinition;
use Plank\Snapshots\Contracts\ManagesVersions;

/**
 * @method SnapshotForeignKeyDefinition deferrable(bool $value = true) Set the foreign key as deferrable (PostgreSQL)
 * @method SnapshotForeignKeyDefinition initiallyImmediate(bool $value = true) Set the default time to check the constraint (PostgreSQL)
 * @method SnapshotForeignKeyDefinition on(string $table) Specify the referenced table
 * @method SnapshotForeignKeyDefinition onDelete(string $action) Add an ON DELETE action
 * @method SnapshotForeignKeyDefinition onUpdate(string $action) Add an ON UPDATE action
 * @method SnapshotForeignKeyDefinition references(string|array $columns) Specify the referenced column(s)
 */
class SnapshotForeignKeyDefinition extends ForeignKeyDefinition
{
    /**
     * Specify the referenced table
     */
    public function onSnapshot(string $table): SnapshotForeignKeyDefinition
    {
        $active = $this->getVersionRepository()->active();

        if ($active === null) {
            return $this->on($table);
        }

        return $this->on($active->addTablePrefix($table));
    }

    protected function getVersionRepository(): ManagesVersions
    {
        return app(ManagesVersions::class);
    }
}
