<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Facades\Snapshots;
use Plank\Snapshots\Models\Snapshot;
use Plank\Snapshots\Repository\SnapshotRepository;
use Plank\Snapshots\Tests\TestCase;
use Plank\Snapshots\ValueObjects\VersionNumber;

use function Pest\Laravel\travel;

uses(TestCase::class)->in(__DIR__);

function snapshots(): SnapshotRepository
{
    return Snapshots::getFacadeRoot();
}

function snapshot(string|VersionNumber $number): Snapshot
{
    return Snapshot::query()->where('number', $number)->firstOrFail();
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
 * Create the first Snapshot
 */
function createFirstSnapshot(?string $migrationPath = null): Snapshot
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    travel(1)->minute();

    return Snapshot::factory()->create([
        'number' => '1.0.0',
    ]);
}

/**
 * Create the next Patch Snapshot
 */
function createPatchSnapshot(?string $migrationPath = null): Snapshot
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Snapshot $latest */
    $latest = snapshots()->latest();
    travel(1)->minute();

    $created = Snapshot::factory()->create([
        'number' => $latest->number->nextPatch(),
    ]);

    travel(1)->minute();

    return $created;
}

/**
 * Create the next Minor Snapshot
 */
function createMinorSnapshot(?string $migrationPath = null): Snapshot
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Snapshot $latest */
    $latest = snapshots()->latest();
    travel(1)->minute();

    $created = Snapshot::factory()->create([
        'number' => $latest->number->nextMinor(),
    ]);

    travel(1)->minute();

    return $created;
}

/**
 * Create the next Major Snapshot
 */
function createMajorSnapshot(?string $migrationPath = null): Snapshot
{
    if ($migrationPath) {
        setMigrationPath($migrationPath);
    }

    /** @var Snapshot $latest */
    $latest = snapshots()->latest();
    travel(1)->minute();

    $created = Snapshot::factory()->create([
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
