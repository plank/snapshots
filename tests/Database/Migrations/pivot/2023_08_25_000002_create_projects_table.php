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
        Schema::create('projects', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->string('name');
            $table->timestamps();
        });
    }
};
