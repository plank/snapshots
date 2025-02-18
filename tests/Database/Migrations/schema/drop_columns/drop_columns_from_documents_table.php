<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        if (version_compare(app()->version(), '11.0.0', '>=')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropIndex(['released_at']);
            });
        }

        Schema::dropColumns('documents', ['released_at']);
    }
};
