<?php

namespace Plank\Snapshots;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\VersionException;
use Plank\Snapshots\Listeners\CopyTable;
use Plank\Snapshots\Listeners\SnapshotDatabase;
use Plank\Snapshots\Migrator\SnapshotMigrator;
use Plank\Snapshots\Migrator\SnapshotSchemaBuilder;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
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
            ->hasConfigFile()
            ->hasMigrations([
                'create_versions_table',
            ])
            ->hasInstallCommand(function(InstallCommand $command) {
                $command->startWith(function(InstallCommand $command) {
                    $command->info("📸  Laravel Snapshots Installer... \n");

                    if ($command->confirm("Would you like to publish the config file?")) {
                        $command->publishConfigFile();
                    }
    
                    if ($command->confirm("Would you like to publish the migrations?")) {
                        $command->publishMigrations();
                    }
    
                    $command->askToRunMigrations();
                });

                $command->endWith(function(InstallCommand $command) {
                    $command->info('✅ Installation complete.');

                    $command->askToStarRepoOnGitHub('plank/snapshots');
                });
                   
            });
    }

    public function bootingPackage()
    {
        $this->bindVersion()
            ->bindRepository()
            ->bindSchemaBuilder()
            ->bindMigrator();

        $this->listenToEvents();
    }

    protected function bindVersion(): self
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

        return $this;
    }

    protected function bindRepository(): self
    {
        if (! $this->app->bound(ManagesVersions::class)) {
            $this->app->scoped(ManagesVersions::class, function (Application $app) {
                $repo = $app['config']->get('snapshots.repository');

                return new $repo;
            });
        }

        return $this;
    }

    protected function bindSchemaBuilder(): self
    {
        if (! $this->app->bound(SnapshotSchemaBuilder::class)) {
            $this->app->scoped(SnapshotSchemaBuilder::class, function (Application $app) {
                $schema = $this->app['db']->connection()->getSchemaBuilder();
                $connection = $schema->getConnection();

                return new SnapshotSchemaBuilder(
                    $connection,
                    $app[ManagesVersions::class]
                );
            });
        }

        return $this;
    }

    protected function bindMigrator(): self
    {
        $this->app->extend('migrator', function (Migrator $migrator, Application $app) {
            return new SnapshotMigrator(
                $app['migration.repository'],
                $app['db'],
                $app['files'],
                $app['events'],
                $app[ManagesVersions::class],
                $app[Version::class]
            );
        });

        return $this;
    }

    protected function listenToEvents(): self
    {
        if (config('snapshots.auto_migrate') === true) {
            Event::listen(VersionCreated::class, SnapshotDatabase::class);
        }

        if (config('snapshots.auto_copy') === true) {
            Event::listen(TableCreated::class, CopyTable::class);
        }

        return $this;
    }
}
