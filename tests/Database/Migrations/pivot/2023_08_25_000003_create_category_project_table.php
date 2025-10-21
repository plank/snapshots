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
        Schema::create('category_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unversionedForeignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignUlid('project_id')->constrained('projects', 'ulid');
            $table->timestamps();
        });
    }
};
