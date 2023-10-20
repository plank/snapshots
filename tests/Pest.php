<?php

use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\Tests\TestCase;

use function Pest\Laravel\travel;

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
 * Create the first Version
 */
function createFirstVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    travel(1)->minute();

    return Version::factory()->create([
        'number' => '1.0.0',
    ]);
}

/**
 * Create the next Patch Version
 */
function createPatchVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    return Version::factory()->create([
        'number' => $latest->number->nextPatch(),
    ]);
}

/**
 * Create the next Minor Version
 */
function createMinorVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    return Version::factory()->create([
        'number' => $latest->number->nextMinor(),
    ]);
}

/**
 * Create the next Major Version
 */
function createMajorVersion(string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    return Version::factory()->create([
        'number' => $latest->number->nextMajor(),
    ]);
}
