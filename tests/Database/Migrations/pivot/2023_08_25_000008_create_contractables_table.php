<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->create('contractables', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->morphs('contractable');
            $table->timestamps();

            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
        });
    }
};
