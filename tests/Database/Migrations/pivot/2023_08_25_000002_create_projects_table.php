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
        Schema::create('projects', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->string('name');
            $table->timestamps();
        });
    }
};
