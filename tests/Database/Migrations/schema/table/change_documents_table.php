<?php

use Illuminate\Database\Schema\Blueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->table('documents', function (Blueprint $table) {
            $table->text('text')->change();
        });
    }
};
