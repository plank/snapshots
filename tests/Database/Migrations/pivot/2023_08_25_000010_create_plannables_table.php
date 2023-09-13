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
        $this->schema->create('plannables', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->morphs('plannable');
            $table->boolean('accepted')->default(false);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->onSnapshot('plans')->onDelete('cascade');
        });
    }
};
