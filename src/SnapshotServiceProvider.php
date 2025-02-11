<?php

namespace Plank\Snapshots;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Factory\SnapshotConnectionBuilder;
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
            ->bindConnectionBuilder()
            ->bindMigrator()
            ->listenToEvents();
    }

    protected function bindRepositories(): self
    {
        $this->app->scopedIf(ManagesVersions::class, function (Application $app) {
            $repo = $app['config']->get('snapshots.repositories.version');

            return new $repo;
        });

        $this->app->scopedIf(ResolvesCauser::class, function (Application $app) {
            $repo = $app['config']->get('snapshots.repositories.causer');

            return new $repo;
        });

        $this->app->scopedIf(ManagesCreatedTables::class, function (Application $app) {
            $repo = $app['config']->get('snapshots.repositories.table');

            return new $repo;
        });

        return $this;
    }

    protected function bindConnectionBuilder(): self
    {
        $this->app->scopedIf(SnapshotConnectionBuilder::class, function (Application $app) {
            return new SnapshotConnectionBuilder(
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
                $app['migration.repository'],
                $app['db'],
                $app['files'],
                $app['events'],
                $app[SnapshotConnectionBuilder::class],
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
