<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;

return new class extends Migration implements SnapshotMigration
{
    public function up()
    {
        if (version_compare(app()->version(), '11.0.0', '>=')) {
            Schema::table('documents', function (SnapshotBlueprint $table) {
                $table->dropIndex(['released_at']);
            });
        }

        Schema::dropColumns('documents', ['released_at']);
    }
};
