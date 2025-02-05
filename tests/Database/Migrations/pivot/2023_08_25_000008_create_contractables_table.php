<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Migrator\SnapshotBlueprint;

return new class extends Migration implements SnapshotMigration
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

            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
        });
    }
};
