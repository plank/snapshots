<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        Schema::create('document_signature', function (SnapshotBlueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained();
            $table->foreignId('signature_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('document_signature');
    }
};
