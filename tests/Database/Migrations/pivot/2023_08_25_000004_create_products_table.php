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
        $this->schema->create('products', function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('price_in_cents');
            $table->timestamps();
        });
    }
};
