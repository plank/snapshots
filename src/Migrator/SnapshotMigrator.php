<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Facades\Snapshots;

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

                if (! $this->snapshotModelHasBeenMigrated()) {
                    return config()->get('snapshots.force_snapshots')
                        ? null
                        : $this->versionedFile(in_array($name, $ran) ? null : $file);
                }

                return Snapshots::all()
                    ->map(function (Snapshot $snapshot) use ($file, $ran) {
                        $name = $this->addMigrationPrefix($snapshot, $this->getMigrationName($file));

                        return in_array($name, $ran) ? null : $this->versionedFile($file, $snapshot);
                    })
                    ->when(
                        ! config()->get('snapshots.force_snapshots'),
                        fn (Collection $collection) => $collection->prepend($this->versionedFile(in_array($name, $ran) ? null : $file)),
                    )
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
    protected function snapshotModelHasBeenMigrated(): bool
    {
        return $this->usingConnectionSchema(
            $this->resolver->connection(),
            fn () => Snapshots::model()->hasBeenMigrated()
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
        [$file, $snapshotKey] = str_contains($file, '@snapshot:')
            ? explode('@snapshot:', $file)
            : [$file, null];

        $migration = $this->resolvePath($file);

        if ($migration instanceof SnapshotMigration) {
            $this->resolver->purge($migration->getConnection());
        }

        Snapshots::withSnapshotActive(
            $snapshotKey ? Snapshots::find($snapshotKey) : null,
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
        $ran = $this->repository->getMigrations(count($this->repository->getRan()));
        $migrations = $this->allMatchingMigrations($migrations, $ran);

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

    protected function allMatchingMigrations(array $migrations, array $ran): array
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

        Snapshots::withSnapshotActive(
            Snapshots::byKey($migration->migration),
            fn () => parent::runDown($file, $migration, $pretend),
        );

        if ($migration instanceof SnapshotMigration) {
            $this->app->instance('db.schema', $this->resolver->connection($this->connection)->getSchemaBuilder());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function addMigrationPrefix((Snapshot&Model)|null $snapshot, string $migration): string
    {
        if ($snapshot === null) {
            return $migration;
        }

        return $snapshot->key()->prefix($migration);
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

    protected function versionedFile(?string $file, (Snapshot&Model)|null $snapshot = null): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file.'@snapshot:'.$snapshot?->getKey();
    }

    protected function unversionedFile(string $file): string
    {
        return (string) str()->before($file, '@snapshot:');
    }

    /**
     * Clear all migration paths from the migrator
     */
    public function clearPaths(): void
    {
        $this->paths = [];
    }
}
