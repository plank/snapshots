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
        $this->schema->create('post_post', function (SnapshotBlueprint $table) {
            $table->foreignUuid('post_id')->constrainedToSnapshot('posts', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('related_id')->constrainedToSnapshot('posts', 'uuid')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['post_id', 'related_id']);
        });
    }
};
