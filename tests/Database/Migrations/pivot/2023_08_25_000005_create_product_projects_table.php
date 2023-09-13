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
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->onSnapshot('products')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->onSnapshot('projects')->onDelete('cascade');
        });
    }
};
