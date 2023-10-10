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
        $this->schema->create('product_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrainedToSnapshot('products');
            $table->foreignUlid('project_id')->constrainedToSnapshot('projects', 'ulid');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }
};
