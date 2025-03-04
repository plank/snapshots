<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::create('documents', function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('documents');
    }
};
