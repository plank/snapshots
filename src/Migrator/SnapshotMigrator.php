<?php

namespace Plank\Snapshots\Migrator;

use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use ReflectionClass;

class SnapshotMigrator extends Migrator
{
    public ManagesVersions $versions;

    public function __construct(
        MigrationRepositoryInterface $repository,
        ConnectionResolverInterface $resolver,
        Filesystem $files,
        Dispatcher $dispatcher = null,
        ManagesVersions $versions
    ) {
        parent::__construct($repository, $resolver, $files, $dispatcher);
        $this->versions = $versions;
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
                $toRun = in_array($name, $ran) ? null : $file;

                if (! $migration instanceof SnapshotMigration) {
                    return $toRun;
                }

                $toRun = $toRun ? $toRun.'@version:' : null;

                $versionModel = app()->make(Version::class);

                if (! $versionModel->hasBeenMigrated()) {
                    return $toRun;
                }

                return $this->versions->all()->map(function (Version $version) use ($file, $ran) {
                    $name = $version->addMigrationPrefix($this->getMigrationName($file));

                    return in_array($name, $ran) ? null : $file.'@version:'.$version->getKey();
                })
                    ->prepend($toRun)
                    ->values()
                    ->all();
            })
            ->flatten()
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $path = $this->sanitizedFile($file);
            $this->files->requireOnce($path);
        }
    }

    protected function sanitizedFile($file)
    {
        return str($file)->beforeLast('@version:');
    }

    /**
     * {@inheritDoc}
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolvePath($this->sanitizedFile($file));

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

        $name = $version->addMigrationPrefix($this->getMigrationName($file));

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

            $name = $version->addMigrationPrefix($name);

            $this->write(TwoColumnDetail::class, $name);

            $this->write(BulletList::class, collect($this->getQueries($migration, $method))->map(function ($query) {
                return $query['query'];
            }));
        } catch (SchemaException) {
            $this->write(Error::class, sprintf(
                '[%s] failed to dump queries. This may be due to changing database columns using Doctrine, which is not supported while pretending to run migrations.',
                $name,
            ));
        }
    }
}
