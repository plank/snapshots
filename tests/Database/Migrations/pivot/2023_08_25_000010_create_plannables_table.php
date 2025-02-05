<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\SnapshotBlueprint;
use Plank\Snapshots\Contracts\SnapshotMigration;

return new class extends Migration implements SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plannables', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->morphs('plannable');
            $table->boolean('accepted')->default(false);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->onSnapshot('plans')->onDelete('cascade');
        });
    }
};
