<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\SnapshotMigration;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Factory\SnapshotConnectionBuilder;
use ReflectionClass;

class SnapshotMigrator extends Migrator
{
    /**
     * @var array<int,array<int,VersionedConnection|string>>
     */
    protected array $snapshotConnections = [];

    protected VersionedSchema $schema;

    protected SnapshotConnectionBuilder $builder;

    protected ManagesVersions $versions;

    protected ManagesCreatedTables $tables;

    protected Version $version;

    protected Application $app;

    /**
     * @var DatabaseManager
     */
    protected $resolver;

    public function __construct(
        MigrationRepositoryInterface $repository,
        DatabaseManager $resolver,
        Filesystem $files,
        ?Dispatcher $dispatcher,
        SnapshotConnectionBuilder $builder,
        ManagesVersions $versions,
        ManagesCreatedTables $tables,
        Application $app,
    ) {
        parent::__construct($repository, $resolver, $files, $dispatcher);

        $this->schema = $builder->from($resolver->connection())->getSchemaBuilder();
        $this->builder = $builder;
        $this->versions = $versions;
        $this->tables = $tables;
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    protected function pendingMigrations($files, $ran)
    {
        return Collection::make($files)
            ->map(function ($file) use ($ran) {
                $migration = $this->resolvePath($file);
                $name = $this->getMigrationName($file);

                if (! $migration instanceof SnapshotMigration) {
                    return in_array($name, $ran) ? null : $file;
                }

                if (! $this->versionModelHasBeenMigrated()) {
                    return $this->versionedFile(in_array($name, $ran) ? null : $file);
                }

                return $this->versions->all()
                    ->map(function (Version $version) use ($file, $ran) {
                        $name = $this->addMigrationPrefix($version, $this->getMigrationName($file));

                        return in_array($name, $ran) ? null : $this->versionedFile($file, $version);
                    })
                    ->prepend($this->versionedFile(in_array($name, $ran) ? null : $file))
                    ->values()
                    ->all();
            })
            ->flatten()
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Detrmine if the configured version model has been migrated yet
     */
    protected function versionModelHasBeenMigrated(): bool
    {   
        return $this->usingConnectionSchema(
            $this->resolver->connection(),
            fn () => $this->versions->model()->hasBeenMigrated()
        );
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($this->unversionedFile($file));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolvePath($this->unversionedFile($file));

        if (! $migration instanceof SnapshotMigration) {
            return parent::runUp($file, $batch, $pretend);
        }

        [$file, $versionKey] = explode('@version:', $file);

        $this->versions->withVersionActive(
            $versionKey ? $this->versions->find($versionKey) : null,
            fn ($version) => $this->runVersionedUp($version, $migration, $file, $batch, $pretend)
        );
    }

    protected function runVersionedUp(?Version $version, SnapshotMigration $migration, string $file, int $batch, bool $pretend)
    {
        $name = $this->addMigrationPrefix($version, $this->getMigrationName($file));

        if ($pretend) {
            return $this->pretendToRunVersion($version, $migration, 'up');
        }

        $this->write(Task::class, $name, fn () => $this->runMigration($migration, 'up'));

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($name, $batch);
    }

    /**
     * {@inheritDoc}
     */
    protected function rollbackMigrations(array $migrations, $paths, array $options)
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        $this->fireMigrationEvent(new MigrationsStarted('down'));

        $this->write(Info::class, 'Rolling back migrations.');

        // Since we are dealing with individual migrations which may have run accross many
        // batches, for consistency we need to apply all down operations to every version
        // of the migration.
        $ran = $this->repository->getMigrations($this->repository->getNextBatchNumber() - 1);
        $migrations = $this->includingPreviousBatches($migrations, $ran);

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order.
        foreach ($migrations as $migration) {
            if (! $file = Arr::get($files, $this->stripMigrationPrefix($migration->migration))) {
                $this->write(TwoColumnDetail::class, $migration->migration, '<fg=yellow;options=bold>Migration not found</>');

                continue;
            }

            $this->runDown($file, $migration, $options['pretend'] ?? false);

            if (! in_array($file, $rolledBack)) {
                $rolledBack[] = $file;
            }
        }

        $this->fireMigrationEvent(new MigrationsEnded('down'));

        return $rolledBack;
    }

    protected function includingPreviousBatches(array $migrations, array $ran): array
    {
        $stripped = collect($migrations)
            ->map(fn ($migration) => (object) $migration)
            ->map(fn ($migration) => $this->stripMigrationPrefix($migration->migration))
            ->unique();

        return collect($ran)
            ->map(fn ($migration) => (object) $migration)
            ->filter(fn ($migration) => $stripped->contains($this->stripMigrationPrefix($migration->migration)))
            ->sortByDesc('batch')
            ->groupBy('batch')
            ->map(fn ($migrations) => $migrations->sortByDesc(fn ($migration) => $migration->id ?? $migration->migration))
            ->flatten()
            ->values()
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    protected function runDown($file, $migration, $pretend)
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolvePath($file);

        if (! $instance instanceof SnapshotMigration) {
            return parent::runDown($file, $migration, $pretend);
        }

        $this->versions->withVersionActive(
            $this->versions->byKey($migration->migration),
            fn ($version) => $this->runVersionedDown($version, $migration, $instance, $file, $pretend)
        );
    }

    protected function runVersionedDown(?Version $version, $migration, SnapshotMigration $instance, string $file, bool $pretend)
    {
        $name = $this->addMigrationPrefix($version, $this->getMigrationName($file));

        if ($pretend) {
            if ($migration->migration === 'create_documents_table') {
                $GLOBALS['dd'] = true;
            }

            return $this->pretendToRunVersion($version, $instance, 'down');
        }

        $this->write(Task::class, $name, fn () => $this->runMigration($instance, 'down'));

        $this->repository->delete($migration);
    }

    /**
     * Pretend to run the migrations.
     *
     * @param  object  $migration
     * @return void
     */
    public function pretendToRunVersion(?Version $version, SnapshotMigration $migration, string $method)
    {
        try {
            $name = get_class($migration);

            $reflectionClass = new ReflectionClass($migration);

            if ($reflectionClass->isAnonymous()) {
                $name = $this->getMigrationName($reflectionClass->getFileName());
            }

            $name = $this->addMigrationPrefix($version, $name);

            $this->write(TwoColumnDetail::class, $name);

            $this->write(BulletList::class, collect($this->getVersionedQueries($migration, $method))->map(function ($query) {
                return $query['query'];
            }));

            $this->tables->flush();
        } catch (SchemaException) {
            $this->write(Error::class, sprintf(
                '[%s] failed to dump queries. This may be due to changing database columns using Doctrine, which is not supported while pretending to run migrations.',
                $name,
            ));
        }
    }

    /**
     * Get all of the queries that would be run for a migration.
     *
     * @param  object  $migration
     * @param  string  $method
     * @return array
     */
    protected function getVersionedQueries($migration, $method)
    {
        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        [$name, $db] = $this->snapshotConnection($this->resolveConnection(
            $migration->getConnection()
        ));

        return $db->pretend(function () use ($db, $migration, $method) {
            if (method_exists($migration, $method)) {
                $this->runMethod($db, $migration, $method);
            }
        });
    }

    /**
     * Pretend to run the migrations.
     *
     * @param  object  $migration
     * @param  string  $method
     * @return void
     */
    protected function pretendToRun($migration, $method)
    {
        parent::pretendToRun($migration, $method);
        $this->tables->flush();
    }

    protected function runMethod($connection, $migration, $method)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        try {
            [$name, $connection] = $migration instanceof SnapshotMigration
                ? $this->snapshotConnection($connection)
                : [$connection->getName(), $connection];

            $this->resolver->setDefaultConnection($name);

            $this->usingConnectionSchema($connection, fn () => $migration->{$method}());
        } finally {
            $this->resolver->setDefaultConnection($previousConnection);
        }
    }

    protected function snapshotConnection(Connection $connection): array
    {
        $name = $connection->getName().'_snapshots';

        if (isset($this->snapshotConnections[$name])) {
            return $this->snapshotConnections[$name];
        }

        $this->app['config']->set('database.connections.'.$name, $connection->getConfig());

        $connection = $this->builder->from($connection);

        $this->resolver->extend($name, fn () => $connection);

        $this->snapshotConnections[$name] = [$name, $connection];

        return $this->snapshotConnections[$name];
    }

    /**
     * @template TReturn
     *
     * @param callable(): TReturn $callback
     * @return TReturn
     */
    protected function usingConnectionSchema(Connection $connection, Closure $callback): mixed
    {
        $previousSchema = $this->app->make('db.schema');

        try {
            $this->app->instance('db.schema', $connection->getSchemaBuilder());

            $result = $callback();
        } finally {
            $this->app->instance('db.schema', $previousSchema);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function addMigrationPrefix(?Version $version, string $migration): string
    {
        if ($version === null) {
            return $migration;
        }

        return $version->key()->prefix($migration);
    }

    /**
     * {@inheritDoc}
     */
    protected function stripMigrationPrefix(string $migration): string
    {
        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config('snapshots.value_objects.version_key');

        return $keyClass::strip($migration);
    }

    protected function versionedFile(?string $file, ?Version $version = null): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file.'@version:'.$version?->getKey();
    }

    protected function unversionedFile(string $file): string
    {
        return (string) str()->before($file, '@version:');
    }

    /**
     * Clear all migration paths from the migrator
     */
    public function clearPaths(): void
    {
        $this->paths = [];
    }
}
