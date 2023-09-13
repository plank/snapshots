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
        $this->schema->create('categories', function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
};
