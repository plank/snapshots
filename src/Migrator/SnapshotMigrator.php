<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Facades\Versions;

class SnapshotMigrator extends Migrator
{
    /**
     * @var DatabaseManager
     */
    protected $resolver;

    protected Application $app;

    public function __construct(
        MigrationRepositoryInterface $repository,
        DatabaseManager $resolver,
        Filesystem $files,
        ?Dispatcher $dispatcher,
        Application $app,
    ) {
        parent::__construct($repository, $resolver, $files, $dispatcher);

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

                return Versions::all()
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
            fn () => Versions::model()->hasBeenMigrated()
        );
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
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
    protected function runUp($file, $batch, $pretend)
    {
        [$file, $versionKey] = str_contains($file, '@version:')
            ? explode('@version:', $file)
            : [$file, null];

        $migration = $this->resolvePath($file);

        if ($migration instanceof SnapshotMigration) {
            $this->resolver->purge($migration->getConnection());
        }

        Versions::withVersionActive(
            $versionKey ? Versions::find($versionKey) : null,
            fn () => parent::runUp($file, $batch, $pretend)
        );

        if ($migration instanceof SnapshotMigration) {
            $this->app->instance('db.schema', $this->resolver->connection($this->connection)->getSchemaBuilder());
        }
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
        $instance = $this->resolvePath($file);

        if ($instance instanceof SnapshotMigration) {
            $this->resolver->purge($instance->getConnection());
        }

        Versions::withVersionActive(
            Versions::byKey($migration->migration),
            fn () => parent::runDown($file, $migration, $pretend),
        );

        if ($migration instanceof SnapshotMigration) {
            $this->app->instance('db.schema', $this->resolver->connection($this->connection)->getSchemaBuilder());
        }
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
