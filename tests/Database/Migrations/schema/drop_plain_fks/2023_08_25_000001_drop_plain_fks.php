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
        // Unsupported in SQLite
        Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
            $table->dropPlainForeign(['plain_id']);
        });

        Schema::table('snapshotted_alsos', function (SnapshotBlueprint $table) {
            $table->dropPlainForeignIdFor(PlainAlso::class);
        });

        Schema::table('snapshotted_ulids', function (SnapshotBlueprint $table) {
            $table->dropPlainForeign(['plain_ulid_id']);
        });

        Schema::table('snapshotted_ulid_alsos', function (SnapshotBlueprint $table) {
            $table->dropPlainForeignIdFor(PlainUlidAlso::class);
        });

        Schema::table('snapshotted_uuids', function (SnapshotBlueprint $table) {
            $table->dropPlainForeign(['plain_uuid_id']);
        });

        Schema::table('snapshotted_uuid_alsos', function (SnapshotBlueprint $table) {
            $table->dropPlainForeignIdFor(PlainUuidAlso::class);
        });
    }
};
