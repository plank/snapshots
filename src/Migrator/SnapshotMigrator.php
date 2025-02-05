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
use Illuminate\Database\ConnectionResolverInterface;
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
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Factory\SchemaBuilderFactory;
use ReflectionClass;

class SnapshotMigrator extends Migrator
{
    protected VersionedSchema $schema;

    protected ManagesVersions $versions;

    protected ManagesCreatedTables $tables;

    protected Version $version;

    protected Application $app;

    public function __construct(
        VersionedSchema $schema,
        MigrationRepositoryInterface $repository,
        ConnectionResolverInterface $resolver,
        Filesystem $files,
        ?Dispatcher $dispatcher,
        ManagesVersions $versions,
        ManagesCreatedTables $tables,
        Application $app,
    ) {
        parent::__construct($repository, $resolver, $files, $dispatcher);

        $this->schema = $schema;
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

                if (! $this->versionHasBeenMigrated()) {
                    return $this->versionedFile(in_array($name, $ran) ? null : $file);
                }

                return $this->versions->all()
                    ->map(function (Version $version) use ($file, $ran) {
                        $name = $this->schema->addMigrationPrefix($version, $this->getMigrationName($file));

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
    protected function versionHasBeenMigrated(): bool
    {
        /** @var class-string<Version> $class */
        $class = config('snapshots.models.version');

        return (new $class)->hasBeenMigrated();
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

        $active = $this->versions->active();

        [$file, $versionKey] = explode('@version:', $file);

        if ($versionKey) {
            $version = $this->versions->find($versionKey);
            $this->runVersionedUp($version, $migration, $file, $batch, $pretend);
        } else {
            $this->versions->clearActive();
            parent::runUp($file, $batch, $pretend);
        }

        $this->versions->setActive($active);
    }

    protected function runVersionedUp(Version $version, SnapshotMigration $migration, string $file, int $batch, bool $pretend)
    {
        $this->versions->setActive($version);

        $name = $this->schema->addMigrationPrefix($version, $this->getMigrationName($file));

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
            if (! $file = Arr::get($files, $this->schema->stripMigrationPrefix($migration->migration))) {
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
            ->map(fn ($migration) => $this->schema->stripMigrationPrefix($migration->migration))
            ->unique();

        return collect($ran)
            ->map(fn ($migration) => (object) $migration)
            ->filter(fn ($migration) => $stripped->contains($this->schema->stripMigrationPrefix($migration->migration)))
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

        $active = $this->versions->active();

        $version = $this->schema->versionFromMigration($migration->migration);

        if ($version === null) {
            $this->versions->clearActive();
            parent::runDown($file, $migration, $pretend);
        } else {
            $this->runVersionedDown($version, $migration, $instance, $file, $pretend);
        }

        $this->versions->setActive($active);
    }

    protected function runVersionedDown(Version $version, $migration, SnapshotMigration $instance, string $file, bool $pretend)
    {
        $this->versions->setActive($version);

        $name = $this->schema->addMigrationPrefix($version, $this->getMigrationName($file));

        if ($pretend) {
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
    public function pretendToRunVersion(Version $version, SnapshotMigration $migration, string $method)
    {
        try {
            $name = get_class($migration);

            $reflectionClass = new ReflectionClass($migration);

            if ($reflectionClass->isAnonymous()) {
                $name = $this->getMigrationName($reflectionClass->getFileName());
            }

            $name = $this->schema->addMigrationPrefix($version, $name);

            $this->write(TwoColumnDetail::class, $name);

            $this->write(BulletList::class, collect($this->getQueries($migration, $method))->map(function ($query) {
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
            $this->resolver->setDefaultConnection($connection->getName());

            if ($migration instanceof SnapshotMigration) {
                $this->usingSnapshotSchemaBuilder(fn () => $migration->{$method}());
            } else {
                $migration->{$method}();
            }
        } finally {
            $this->resolver->setDefaultConnection($previousConnection);
        }
    }

    protected function usingSnapshotSchemaBuilder(Closure $callback)
    {
        $active = $this->app->make('db.schema');

        $this->app->instance('db.schema', SchemaBuilderFactory::make(
            $this->app['db.connection'],
            $this->app[ManagesVersions::class],
            $this->app[ManagesCreatedTables::class],
        ));

        try {
            return $callback();
        } finally {
            $this->app->instance('db.schema', $active);
        }
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
