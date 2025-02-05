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
        Schema::create('posts', function (SnapshotBlueprint $table) {
            $table->uuid()->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('slug');
            $table->string('title');
            $table->string('body');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
};
