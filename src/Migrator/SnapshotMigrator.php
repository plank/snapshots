<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Plank\Snapshots\Connection\SchemaGrammar;
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
                    return config()->get('snapshots.force_versions')
                        ? null
                        : $this->versionedFile(in_array($name, $ran) ? null : $file);
                }

                return Versions::all()
                    ->map(function (Version $version) use ($file, $ran) {
                        $name = $this->addMigrationPrefix($version, $this->getMigrationName($file));

                        return in_array($name, $ran) ? null : $this->versionedFile($file, $version);
                    })
                    ->when(
                        ! config()->get('snapshots.force_versions'),
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
    protected function versionModelHasBeenMigrated(): bool
    {
        return Versions::model()->hasBeenMigrated();
    }

    /**
     * {@inheritDoc}
     */
    protected function runUp($file, $batch, $pretend)
    {
        [$file, $versionKey] = str_contains($file, '@version:')
            ? explode('@version:', $file)
            : [$file, null];

        $version = $versionKey ? Versions::find($versionKey) : null;

        Versions::withVersionActive($version, fn () => parent::runUp($file, $batch, $pretend));
    }

    /**
     * {@inheritDoc}
     *
     * Sets the versioned prefix for pretend mode, which bypasses runMigration.
     */
    protected function getQueries($migration, $method)
    {
        if (! $migration instanceof SnapshotMigration) {
            return parent::getQueries($migration, $method);
        }

        return $this->withVersionedConnection($migration, fn () => parent::getQueries($migration, $method));
    }

    /**
     * {@inheritDoc}
     */
    protected function runMigration($migration, $method, $name = null)
    {
        if (! $migration instanceof SnapshotMigration) {
            parent::runMigration($migration, $method, $name);

            return;
        }

        $this->withVersionedConnection($migration, fn () => parent::runMigration($migration, $method, $name));
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withVersionedConnection(SnapshotMigration $migration, Closure $callback): mixed
    {
        $connection = $this->resolver->connection($migration->getConnection());
        $version = Versions::active();

        $originalGrammar = $connection->getSchemaGrammar();
        SchemaGrammar::useSnapshots($connection);

        $originalPrefix = $connection->getTablePrefix();

        if ($version) {
            $connection->setTablePrefix($version->key()->prefix($originalPrefix));
        }

        try {
            return $callback();
        } finally {
            $connection->setTablePrefix($originalPrefix);

            if ($originalGrammar !== null) {
                $connection->setSchemaGrammar($originalGrammar);
            } else {
                $connection->useDefaultSchemaGrammar();
            }
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
        Versions::withVersionActive(
            Versions::byKey($migration->migration),
            fn () => parent::runDown($file, $migration, $pretend),
        );
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
