<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;

return new class extends Migration implements SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrainedToSnapshot('products');
            $table->foreignUlid('project_id')->constrainedToSnapshot('projects', 'ulid');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }
};
