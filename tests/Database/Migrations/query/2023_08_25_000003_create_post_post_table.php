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
        Schema::create('post_post', function (SnapshotBlueprint $table) {
            $table->foreignUuid('post_id')->constrainedToSnapshot('posts', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('related_id')->constrainedToSnapshot('posts', 'uuid')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['post_id', 'related_id']);
        });
    }
};
