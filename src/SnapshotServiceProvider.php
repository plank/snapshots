<?php

namespace Plank\Snapshots;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Factory\SchemaBuilderFactory;
use Plank\Snapshots\Migrator\SnapshotMigrator;
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
                'create_history_table',
                'create_versions_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->startWith(function (InstallCommand $command) {
                    $command->info("📸  Laravel Snapshots Installer... \n");

                    if ($command->confirm('Would you like to publish the config file?')) {
                        $command->publishConfigFile();
                    }

                    if ($command->confirm('Would you like to publish the migrations?')) {
                        $command->publishMigrations();
                    }

                    $command->askToRunMigrations();
                });

                $command->endWith(function (InstallCommand $command) {
                    $command->info('✅ Installation complete.');

                    $command->askToStarRepoOnGitHub('plank/snapshots');
                });
            });
    }

    public function bootingPackage()
    {
        $this->bindRepositories()
            ->bindSchemaBuilder()
            ->bindMigrator();

        $this->listenToEvents();
    }

    protected function bindRepositories(): self
    {
        if (! $this->app->bound(ManagesVersions::class)) {
            $this->app->scoped(ManagesVersions::class, function (Application $app) {
                $repo = $app['config']->get('snapshots.repositories.version');

                return new $repo;
            });
        }

        if (! $this->app->bound(ResolvesCauser::class)) {
            $this->app->scoped(ResolvesCauser::class, function (Application $app) {
                $repo = $app['config']->get('snapshots.repositories.causer');

                return new $repo;
            });
        }

        if (! $this->app->bound(ManagesCreatedTables::class)) {
            $this->app->scoped(ManagesCreatedTables::class, function (Application $app) {
                $repo = $app['config']->get('snapshots.repositories.table');

                return new $repo;
            });
        }

        return $this;
    }

    protected function bindSchemaBuilder(): self
    {
        if ($this->app->bound(VersionedSchema::class)) {
            return $this;
        }

        $this->app->scoped(VersionedSchema::class, function (Application $app) {
            return SchemaBuilderFactory::make(
                $app['db.connection'],
                $app[ManagesVersions::class],
                $app[ManagesCreatedTables::class],
            );
        });

        return $this;
    }

    protected function bindMigrator(): self
    {
        $this->app->extend('migrator', function (Migrator $migrator, Application $app) {
            return new SnapshotMigrator(
                $app[VersionedSchema::class],
                $app['migration.repository'],
                $app['db'],
                $app['files'],
                $app['events'],
                $app[ManagesVersions::class],
                $app[ManagesCreatedTables::class],
                $app,
            );
        });

        return $this;
    }

    protected function listenToEvents(): self
    {
        if ($migrator = config()->get('snapshots.auto_migrator')) {
            Event::listen(VersionCreated::class, $migrator);
        }

        if ($copier = config()->get('snapshots.copier.handler')) {
            Event::listen(MigrationsEnded::class, $copier);
        }

        if ($labeler = config()->get('snapshots.history.labeler')) {
            Event::listen(TableCopied::class, $labeler);
        }

        return $this;
    }
}
