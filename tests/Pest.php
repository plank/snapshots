<?php

use Carbon\Carbon;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function versions(): VersionRepository
{
    return app(ManagesVersions::class);
}

function setMigrationPath(string $path): void
{
    app('migrator')->path(migrationPath($path));
}

/**
 * Get the path to a tests migration file.
 */
function migrationPath(string $path = ''): string
{
    return realpath(__DIR__).'/Database/Migrations/'.str($path)->trim('/');
}

/**
 * Release the latest Version and return the next Patch Version
 */
function createFirstVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    return Version::factory()->create([
        'number' => '1.0.0',
    ]);
}

/**
 * Release the latest Version and return the next Patch Version
 */
function releaseAndCreatePatchVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    $latest->release();

    moveAheadMinutes(1);

    return Version::factory()->create([
        'number' => $latest->number->nextPatch(),
    ]);
}

/**
 * Release the latest Version and return the next Minor Version
 */
function releaseAndCreateMinorVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    $latest->release();

    moveAheadMinutes(1);

    return Version::factory()->create([
        'number' => $latest->number->nextMinor(),
    ]);
}

/**
 * Release the latest Version and return the next Major Version
 */
function releaseAndCreateMajorVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    $latest->release();

    moveAheadMinutes(1);

    return Version::factory()->create([
        'number' => $latest->number->nextMajor(),
    ]);
}

function moveAheadSeconds(int $seconds)
{
    Carbon::setTestNow(Carbon::now()->addSeconds($seconds));
}

function moveAheadMinutes(int $minutes)
{
    Carbon::setTestNow(Carbon::now()->addMinutes($minutes));
}

function moveAheadHours(int $hours)
{
    Carbon::setTestNow(Carbon::now()->addHours($hours));
}
