<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contractables', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contractor_id');
            $table->morphs('contractable');
            $table->timestamps();

            $table->unversionedForeign('contractor_id')
                ->references('id')
                ->on('contractors')
                ->onDelete('cascade');
        });
    }
};
