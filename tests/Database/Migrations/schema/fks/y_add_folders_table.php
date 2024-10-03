<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->create('folders', function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('disk');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('folders');
    }
};