<?php

use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;
use Plank\Snapshots\Tests\Models\Flag;

return new class extends SnapshotMigration
{
    public function up()
    {
        $this->schema->createForModel(Flag::class, function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        $this->schema->dropForModel(Flag::class);
    }
};
