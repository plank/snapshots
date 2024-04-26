<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        if (version_compare(app()->version(), '11.0.0', '>=')) {
            $this->schema->table('documents', function (SnapshotBlueprint $table) {
                $table->dropIndex(['released_at']);
            });
        }

        $this->schema->dropColumns('documents', ['released_at']);
    }
};
