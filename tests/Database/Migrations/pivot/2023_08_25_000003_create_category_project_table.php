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
        Schema::create('category_project', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignUlid('project_id')->constrainedToSnapshot('projects', 'ulid');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
