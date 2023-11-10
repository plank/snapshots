<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->table('items', function (SnapshotBlueprint $table) {
            $table->boolean('complete')->after('name');
        });
    }

    public function down()
    {
        $this->schema->drop('items');
    }
};
