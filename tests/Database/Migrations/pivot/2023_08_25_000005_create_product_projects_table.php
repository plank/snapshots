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
        Schema::create('product_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignUlid('project_id')->constrained('projects', 'ulid');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }
};
