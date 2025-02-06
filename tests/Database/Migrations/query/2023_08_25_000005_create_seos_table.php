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
