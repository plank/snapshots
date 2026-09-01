<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;
use Plank\Snapshots\Tests\Models\PlainAlso;
use Plank\Snapshots\Tests\Models\PlainUlidAlso;
use Plank\Snapshots\Tests\Models\PlainUuidAlso;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('snapshotteds', function (SnapshotBlueprint $table) {
            $table->id();
            $table->plainForeignId('plain_id')
                ->references('id')
                ->on('plains')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshotted_alsos', function (SnapshotBlueprint $table) {
            $table->id();
            $table->plainForeignIdFor(PlainAlso::class);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshotted_ulids', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->plainForeignUlid('plain_ulid_id')
                ->references('id')
                ->on('plain_ulids')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshotted_ulid_alsos', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->plainForeignIdFor(PlainUlidAlso::class);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshotted_uuids', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->plainForeignUuid('plain_uuid_id')
                ->references('id')
                ->on('plain_uuids')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshotted_uuid_alsos', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->plainForeignIdFor(PlainUuidAlso::class);
            $table->string('name');
            $table->timestamps();
        });
    }
};
