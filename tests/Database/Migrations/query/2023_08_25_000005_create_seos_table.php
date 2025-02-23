<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seos', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('attribute');
            $table->text('value');
            $table->timestamps();

            $table->foreign('post_id')->references('uuid')->on('posts');
        });
    }
};
