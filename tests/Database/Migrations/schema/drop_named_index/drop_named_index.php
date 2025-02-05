<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;

return new class extends Migration implements SnapshotMigration
{
    public function up()
    {
        Schema::table('documents', function (SnapshotBlueprint $table) {
            $table->dropIndex('idx_title');
        });
    }
};
