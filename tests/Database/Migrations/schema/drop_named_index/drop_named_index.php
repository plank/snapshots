<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->table('documents', function (SnapshotBlueprint $table) {
            $table->dropIndex('idx_title');
        });
    }
};
