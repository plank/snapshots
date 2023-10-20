<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;
use Plank\Snapshots\Tests\Models\Document;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->createForModel(Document::class, function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropForModel(Document::class);
    }
};
