<?php

use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Models\Snapshot;
use Plank\Snapshots\ValueObjects\VersionNumber;

describe('Snapshots are migrated correctly', function () {
    it('throws an exception when creating a new snapshot before the previous snapshot has been migrated', function () {
        Snapshot::factory()->createQuietly([
            'number' => '1.0.0',
        ]);

        Snapshot::factory()->create([
            'number' => '1.0.1',
        ]);
    })->throws(MigrationInProgressException::class);

    it('allows you to create a new Snapshot when the previous Snapshot has been migrated', function () {
        createFirstSnapshot();
        createPatchSnapshot();

        expect(snapshots()->latest()->number)->toEqual(VersionNumber::fromString('1.0.1'));
    });

    it('throws an error when trying to cast the snapshot number to a nonstring or value object', function () {
        $snapshot = createFirstSnapshot();

        $snapshot->number = 1;
    })->throws(InvalidArgumentException::class);

    it('returns null when it cannot resolve a snapshot from a migration name', function () {
        createFirstSnapshot();

        expect(snapshots()->byKey('invalid_migration_name'))->toBeNull();
    });
});
