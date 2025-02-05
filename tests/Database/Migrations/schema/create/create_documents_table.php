<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Migrator\SnapshotBlueprint;

return new class extends Migration implements SnapshotMigration
{
    public function up()
    {
        Schema::create('documents', function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('title', 'idx_title');
            $table->index('released_at');
        });
    }

    public function down()
    {
        Schema::drop('documents');
    }
};
