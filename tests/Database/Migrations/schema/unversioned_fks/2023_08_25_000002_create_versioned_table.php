<?php

use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigration;
use Plank\Snapshots\Tests\Models\UnversionedAlso;
use Plank\Snapshots\Tests\Models\UnversionedUlidAlso;
use Plank\Snapshots\Tests\Models\UnversionedUuidAlso;

return new class extends SnapshotMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('versioneds', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unversionedForeignId('unversioned_id')
                ->references('id')
                ->on('unversioneds')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('versioned_alsos', function (SnapshotBlueprint $table) {
            $table->id();
            $table->unversionedForeignIdFor(UnversionedAlso::class);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('versioned_ulids', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->unversionedForeignUlid('unversioned_ulid_id')
                ->references('id')
                ->on('unversioned_ulids')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('versioned_ulid_alsos', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->unversionedForeignIdFor(UnversionedUlidAlso::class);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('versioned_uuids', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->unversionedForeignUuid('unversioned_uuid_id')
                ->references('id')
                ->on('unversioned_uuids')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('versioned_uuid_alsos', function (SnapshotBlueprint $table) {
            $table->ulid('ulid')->primary();
            $table->unversionedForeignIdFor(UnversionedUuidAlso::class);
            $table->string('name');
            $table->timestamps();
        });
    }
};
