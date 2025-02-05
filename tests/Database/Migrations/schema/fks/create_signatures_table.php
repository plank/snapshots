<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Migrator\SnapshotBlueprint;

return new class extends Migration implements SnapshotMigration
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
                ->onSnapshot('documents');
        });
    }

    public function down()
    {
        Schema::drop('signatures');
    }
};
