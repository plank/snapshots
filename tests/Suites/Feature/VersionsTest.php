<?php

use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Exceptions\VersionException;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\ValueObjects\VersionNumber;

describe('Versions are migrated correctly', function () {
    it('throws an exception when you have the version configured incorrectly', function () {
        config()->set('snapshots.models.version', null);

        get_class(app()->make(VersionContract::class));
    })->throws(VersionException::class);

    it('throws an exception when creating a new version before the previous version has been migrated', function () {
        Version::factory()->createQuietly([
            'number' => '1.0.0',
        ]);

        Version::factory()->create([
            'number' => '1.0.1',
        ]);

    })->throws(MigrationInProgressException::class);

    it('allows you to create a new Version when the previous Version has been migrated', function () {
        createFirstVersion();
        createPatchVersion();

        expect(versions()->latest()->number)->toEqual(VersionNumber::fromVersionString('1.0.1'));
    });

    it('throws an error when trying to cast the version number to a nonstring or value object', function () {
        $version = createFirstVersion();

        $version->number = 1;
    })->throws(InvalidArgumentException::class);

    it('returns null when it cannot resolve a version from a migration name', function () {
        $version = createFirstVersion();

        expect($version->resolveVersionFromMigrationName('invalid_migration_name'))->toBeNull();
    });
});
