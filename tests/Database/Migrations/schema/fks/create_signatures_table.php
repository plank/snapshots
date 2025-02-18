<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::create('signatures', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('svg');
            $table->timestamps();

            $table->foreign('document_id')
                ->references('id')
                ->on('documents');
        });
    }

    public function down()
    {
        Schema::drop('signatures');
    }
};
