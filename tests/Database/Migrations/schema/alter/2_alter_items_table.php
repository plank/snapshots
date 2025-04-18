<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::table('items', function (SnapshotBlueprint $table) {
            $table->boolean('complete')->after('name');
        });
    }

    public function down()
    {
        Schema::drop('items');
    }
};
