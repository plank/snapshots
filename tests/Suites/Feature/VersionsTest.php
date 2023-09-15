<?php

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Events\ReleasingVersion;
use Plank\Snapshots\Events\UnreleasingVersion;
use Plank\Snapshots\Events\VersionReleased;
use Plank\Snapshots\Events\VersionUnreleased;
use Plank\Snapshots\Exceptions\AlreadyReleasedException;
use Plank\Snapshots\Exceptions\FutureReleaseException;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Exceptions\UnreleasedVersionException;
use Plank\Snapshots\Exceptions\VersionException;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\ValueObjects\VersionNumber;

describe('Versions are migrated and released correctly', function () {
    it('throws an exception when you have the version configured incorrectly', function () {
        config()->set('snapshots.model', null);

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

    it('throws an exception when creating a new version before the previous version has been released', function () {
        Version::factory()->create([
            'number' => '1.0.0',
        ]);

        Version::factory()->create([
            'number' => '1.0.1',
        ]);
    })->throws(UnreleasedVersionException::class);

    it('allows you to create a new Version when the previous Version has been migrated and released', function () {
        createFirstVersion();
        releaseAndCreatePatchVersion();

        expect(versions()->latest()->number)->toEqual(VersionNumber::fromVersionString('1.0.1'));
    });

    it('fires releasing events when releasing a Version', function () {
        Event::fake([ReleasingVersion::class, VersionReleased::class]);

        createFirstVersion()->release();

        Event::assertDispatched(ReleasingVersion::class, function ($event) {
            return $event->version->number->isEqualTo(VersionNumber::fromVersionString('1.0.0'));
        });

        Event::assertDispatched(VersionReleased::class, function ($event) {
            return $event->version->number->isEqualTo(VersionNumber::fromVersionString('1.0.0'));
        });
    });

    it('fires unreleasing events when unreleasing a Version', function () {
        $version = createFirstVersion();
        $version->release();

        Event::fake([UnreleasingVersion::class, VersionUnreleased::class]);
        $version->unrelease();

        Event::assertDispatched(UnreleasingVersion::class, function ($event) {
            return $event->version->number->isEqualTo(VersionNumber::fromVersionString('1.0.0'));
        });

        Event::assertDispatched(VersionUnreleased::class, function ($event) {
            return $event->version->number->isEqualTo(VersionNumber::fromVersionString('1.0.0'));
        });
    });

    it('No events are fired when not changing the Versions release status', function () {
        $version = createFirstVersion();

        Event::fake([
            UnreleasingVersion::class,
            VersionUnreleased::class,
            ReleasingVersion::class,
            VersionReleased::class,
        ]);

        $version->number = VersionNumber::fromVersionString('0.0.0');
        $version->save();

        Event::assertNotDispatched(UnreleasingVersion::class);
        Event::assertNotDispatched(VersionUnreleased::class);
        Event::assertNotDispatched(ReleasingVersion::class);
        Event::assertNotDispatched(VersionReleased::class);
        Event::assertNotDispatched(ReleasingVersion::class);
    });

    it('throws an exception when attempting to alter a versions release date', function () {
        $version = createFirstVersion();

        $version->released_at = now()->addDay();
        $version->save();
    })->throws(FutureReleaseException::class);

    it('throws an exception when attempting to release a version in the future', function () {
        $version = createFirstVersion();
        $version->release();

        $version->released_at = now()->subDay();
        $version->save();
    })->throws(AlreadyReleasedException::class);

    it('throws an error when trying to cast the version number to a nonstring or value object', function () {
        $version = createFirstVersion();

        $version->number = 1;
    })->throws(InvalidArgumentException::class);

    it('returns null when it cannot resolve a version from a migration name', function () {
        $version = createFirstVersion();

        expect($version->resolveVersionFromMigrationName('invalid_migration_name'))->toBeNull();
    });
});
