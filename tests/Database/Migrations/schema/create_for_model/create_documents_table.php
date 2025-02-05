<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Tests\Models\Document;

return new class extends Migration implements SnapshotMigration
{
    public function up()
    {
        Schema::createForModel(Document::class, function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropForModel(Document::class);
    }
};
