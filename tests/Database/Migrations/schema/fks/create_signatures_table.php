<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->create('signatures', function (SnapshotBlueprint $table) {
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
        $this->schema->drop('signatures');
    }
};
