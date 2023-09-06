<?php

use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->drop('documents');
    }
};
