<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;
use Plank\Snapshots\Tests\Models\Folder;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->table('documents', function (SnapshotBlueprint $table) {
            $table->string('folder_id')->after('id');

            $table->foreign('folder_id')
                ->references('id')
                ->onSnapshot('folders');
        });
    }

    public function down()
    {
        $this->schema->table('documents', function (SnapshotBlueprint $table) {
            $table->dropForeignIdFor(Folder::class);
        });
    }
};
