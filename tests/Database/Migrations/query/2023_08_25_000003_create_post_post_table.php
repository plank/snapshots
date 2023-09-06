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
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('related_id');
            $table->timestamps();

            $table->foreign('post_id')
                ->references('id')
                ->onSnapshot('posts')
                ->cascadeOnDelete();

            $table->foreign('related_id')
                ->references('id')
                ->onSnapshot('posts')
                ->cascadeOnDelete();
        });
    }
};
