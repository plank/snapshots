<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Tests\Models\Flag;

return new class extends Migration implements SnapshotMigration
{
    public function up()
    {
        Schema::createForModel(Flag::class, function (SnapshotBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropForModel(Flag::class);
    }
};
