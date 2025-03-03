<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\Version;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\Tests\TestCase;
use Plank\Snapshots\ValueObjects\VersionNumber;

use function Pest\Laravel\travel;

uses(TestCase::class)->in(__DIR__);

function versions(): VersionRepository
{
    return Versions::getFacadeRoot();
}

function version(string|VersionNumber $number): Version
{
    return Version::query()->where('number', $number)->firstOrFail();
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
function createFirstVersion(?string $migrationPath = null): Version
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
function createPatchVersion(?string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    $created = Version::factory()->create([
        'number' => $latest->number->nextPatch(),
    ]);

    travel(1)->minute();

    return $created;
}

/**
 * Create the next Minor Version
 */
function createMinorVersion(?string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    $created = Version::factory()->create([
        'number' => $latest->number->nextMinor(),
    ]);

    travel(1)->minute();

    return $created;
}

/**
 * Create the next Major Version
 */
function createMajorVersion(?string $migrationPath = null): Version
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Version $latest */
    $latest = versions()->latest();
    travel(1)->minute();

    $created = Version::factory()->create([
        'number' => $latest->number->nextMajor(),
    ]);

    travel(1)->minute();

    return $created;
}

function varcharColumn(): string
{
    if (version_compare(app()->version(), '11.0.0', '>=')) {
        return 'varchar';
    }

    return 'string';
}

/**
 * @template TReturn
 *
 * @param  callable(SchemaBuilder $schema): TReturn  $callback
 * @return TReturn
 */
function usingSnapshotSchema(Closure $callback): mixed
{
    /** @var SchemaBuilder $schema */
    $schema = Schema::getFacadeRoot();
    $connection = $schema->getConnection();
    $name = $connection->getName();

    $name = str($name)->endsWith('_snapshots')
        ? $name
        : $name.'_snapshots';

    DB::purge($name);

    $snapshotConnection = DB::connection($name);

    try {
        return $callback($snapshotConnection->getSchemaBuilder());
    } finally {
        app()->instance('db.schema', $schema);
    }
}
