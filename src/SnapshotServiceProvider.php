<?php

namespace Plank\Snapshots;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\VersionException;
use Plank\Snapshots\Factories\TableCopierFactory;
use Plank\Snapshots\Listeners\SnapshotDatabase;
use Plank\Snapshots\Migrator\SnapshotMigrator;
use Plank\Snapshots\Migrator\SnapshotSchemaBuilder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/*
 * This class is a Package Service Provider
 *
 * More info: https://github.com/spatie/laravel-package-tools
 */
class SnapshotServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('snapshots')
            ->hasConfigFile();
    }

    public function bootingPackage()
    {
        if (! $this->app->bound(Version::class)) {
            $this->app->bind(Version::class, function (Application $app) {
                $model = $app['config']->get('snapshots.model');

                if (! is_a($model, Version::class, true)) {
                    throw VersionException::create($model);
                }

                return new $model;
            });
        }

        if (! $this->app->bound(ManagesVersions::class)) {
            $this->app->scoped(ManagesVersions::class, function (Application $app) {
                $repo = $app['config']->get('snapshots.repository');

                return new $repo;
            });
        }

        if (! $this->app->bound(SnapshotSchemaBuilder::class)) {
            $this->app->scoped(SnapshotSchemaBuilder::class, function (Application $app) {
                dump($app->bound('db.schema'), $this->app->bound('db.schema'));
                $schema = $this->app->make('db.schema');
                $connection = $schema->getConnection();
                $driver = $connection->getDriverName();

                return new SnapshotSchemaBuilder(
                    $connection,
                    TableCopierFactory::forDriver($driver),
                    $app[ManagesVersions::class]
                );
            });
        }

        $this->app->extend('db.schema', function (Builder $schema, Application $app) {

        });

        $this->app->extend('migrator', function (Migrator $migrator, Application $app) {
            return new SnapshotMigrator(
                $app['migration.repository'],
                $app['db'],
                $app['files'],
                $app['events'],
                $app[ManagesVersions::class]
            );
        });

        if (config('snapshots.auto_migrate') === true) {
            Event::listen(VersionCreated::class, SnapshotDatabase::class);
        }
    }
}
