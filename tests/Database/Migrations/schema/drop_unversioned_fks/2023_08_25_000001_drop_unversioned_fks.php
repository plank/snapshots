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
        // Unsupported in SQLite
        Schema::table('versioneds', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeign(['unversioned_id']);
        });

        Schema::table('versioned_alsos', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeignIdFor(UnversionedAlso::class);
        });

        Schema::table('versioned_ulids', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeign(['unversioned_ulid_id']);
        });

        Schema::table('versioned_ulid_alsos', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeignIdFor(UnversionedUlidAlso::class);
        });

        Schema::table('versioned_uuids', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeign(['unversioned_uuid_id']);
        });

        Schema::table('versioned_uuid_alsos', function (SnapshotBlueprint $table) {
            $table->dropUnversionedForeignIdFor(UnversionedUuidAlso::class);
        });
    }
};
