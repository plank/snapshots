<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;

use function Pest\Laravel\artisan;

describe('The snapshot schema works with unversioned foreign keys correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/unversioned_fks'),
            '--realpath' => true,
        ])->run();
    });

    it('creates the unversioned foreign keys correctly', function () {
        snapshots()->setActive(createFirstSnapshot('schema/unversioned_fks'));

        $assertFk = function (array $indexes, string $expected) {
            $columns = collect($indexes)->pluck('columns')->flatten();
            expect($columns)->toContain($expected);
        };

        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('v1_0_0_');
            $assertFk($schema->getForeignKeys('versioneds'), 'unversioned_id');
            $assertFk($schema->getForeignKeys('versioned_alsos'), 'unversioned_also_id');
            $assertFk($schema->getForeignKeys('versioned_ulids'), 'unversioned_ulid_id');
            $assertFk($schema->getForeignKeys('versioned_ulid_alsos'), 'unversioned_ulid_also_id');
            $assertFk($schema->getForeignKeys('versioned_uuids'), 'unversioned_uuid_id');
            $assertFk($schema->getForeignKeys('versioned_uuid_alsos'), 'unversioned_uuid_also_id');
        });

        snapshots()->clearActive();
        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('');
            $assertFk($schema->getForeignKeys('versioneds'), 'unversioned_id');
            $assertFk($schema->getForeignKeys('versioned_alsos'), 'unversioned_also_id');
            $assertFk($schema->getForeignKeys('versioned_ulids'), 'unversioned_ulid_id');
            $assertFk($schema->getForeignKeys('versioned_ulid_alsos'), 'unversioned_ulid_also_id');
            $assertFk($schema->getForeignKeys('versioned_uuids'), 'unversioned_uuid_id');
            $assertFk($schema->getForeignKeys('versioned_uuid_alsos'), 'unversioned_uuid_also_id');
        });
    });

    it('drops the unversioned foreign keys correctly', function () {
        snapshots()->setActive(createFirstSnapshot('schema/unversioned_fks'));

        // SQLite does not support dropping FKs but it will let us run the code
        // so lets at least ensure it doest error out.
        expect(fn () => artisan('migrate', [
            '--path' => migrationPath('schema/drop_unversioned_fks'),
            '--realpath' => true,
        ])->run())->not->toThrow(\Throwable::class);
    });
});
