<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::dropIfExists('documents');
    }
};
