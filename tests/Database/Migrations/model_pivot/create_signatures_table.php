<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::create('signatures', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unversionedForeignId('user_id')->constrained();
            $table->string('file');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('signatures');
    }
};
