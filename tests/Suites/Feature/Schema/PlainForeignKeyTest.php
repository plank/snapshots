<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;

use function Pest\Laravel\artisan;

describe('The snapshot schema works with plain foreign keys correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/plain_fks'),
            '--realpath' => true,
        ])->run();
    });

    it('creates the plain foreign keys correctly', function () {
        snapshots()->setActive(createFirstSnapshot('schema/plain_fks'));

        $assertFk = function (array $indexes, string $expected) {
            $columns = collect($indexes)->pluck('columns')->flatten();
            expect($columns)->toContain($expected);
        };

        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('v1_0_0_');
            $assertFk($schema->getForeignKeys('snapshotteds'), 'plain_id');
            $assertFk($schema->getForeignKeys('snapshotted_alsos'), 'plain_also_id');
            $assertFk($schema->getForeignKeys('snapshotted_ulids'), 'plain_ulid_id');
            $assertFk($schema->getForeignKeys('snapshotted_ulid_alsos'), 'plain_ulid_also_id');
            $assertFk($schema->getForeignKeys('snapshotted_uuids'), 'plain_uuid_id');
            $assertFk($schema->getForeignKeys('snapshotted_uuid_alsos'), 'plain_uuid_also_id');
        });

        snapshots()->clearActive();
        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('');
            $assertFk($schema->getForeignKeys('snapshotteds'), 'plain_id');
            $assertFk($schema->getForeignKeys('snapshotted_alsos'), 'plain_also_id');
            $assertFk($schema->getForeignKeys('snapshotted_ulids'), 'plain_ulid_id');
            $assertFk($schema->getForeignKeys('snapshotted_ulid_alsos'), 'plain_ulid_also_id');
            $assertFk($schema->getForeignKeys('snapshotted_uuids'), 'plain_uuid_id');
            $assertFk($schema->getForeignKeys('snapshotted_uuid_alsos'), 'plain_uuid_also_id');
        });
    });

    it('drops plain foreign keys and columns on snapshotted tables', function () {
        snapshots()->setActive(createFirstSnapshot('schema/plain_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('snapshotteds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('plain_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
                $table->dropPlainForeign(['plain_id']);
                $table->dropColumn('plain_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeFalse();
        });
    });

    it('drops plain foreign keys and columns on plain tables', function () {
        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('snapshotteds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('plain_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
                $table->dropPlainForeign(['plain_id']);
                $table->dropColumn('plain_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeFalse();
        });
    });

    it('drops plain foreign keys without dropping the column', function () {
        snapshots()->setActive(createFirstSnapshot('schema/plain_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('snapshotteds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('plain_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
                $table->dropPlainForeign(['plain_id']);
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeTrue();
            $fkColumns = collect($schema->getForeignKeys('snapshotteds'))->pluck('columns')->flatten();
            expect($fkColumns)->not->toContain('plain_id');
        });
    });

    it('drops constrained plain foreign id on snapshotted tables', function () {
        snapshots()->setActive(createFirstSnapshot('schema/plain_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('snapshotteds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('plain_id');
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeTrue();
        });

        usingSnapshotSchema(function () {
            Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
                $table->dropConstrainedPlainForeignId('plain_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeFalse();
        });
    });

    it('preserves other foreign keys when dropping an plain foreign key', function () {
        snapshots()->setActive(createFirstSnapshot('schema/plain_fks'));

        usingSnapshotSchema(function () {
            Schema::table('snapshotteds', function (SnapshotBlueprint $table) {
                $table->dropPlainForeign(['plain_id']);
                $table->dropColumn('plain_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('snapshotteds', 'plain_id'))->toBeFalse();
            expect($schema->hasTable('snapshotteds'))->toBeTrue();
        });
    });
});
