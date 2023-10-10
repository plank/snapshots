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
        $this->schema->create('category_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignUlid('project_id')->constrainedToSnapshot('projects', 'ulid');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
