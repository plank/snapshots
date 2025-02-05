<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;

return new class extends Migration implements SnapshotMigration
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
