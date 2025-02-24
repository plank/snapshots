<?php

namespace Plank\Snapshots;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Plank\LaravelSchemaEvents\Events\TableCreated;
use Plank\Snapshots\Connection\SnapshotConnectionInitializer;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Migrator\Blueprint\Macros\DropUnversionedForeignKey;
use Plank\Snapshots\Migrator\Blueprint\Macros\UnversionedForeignKey;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;
use Plank\Snapshots\Migrator\SnapshotMigrationRepository;
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
            ->bindMigrator()
            ->registerGrammarMacros()
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

        return $this;
    }

    protected function bindMigrator(): self
    {
        $this->app->extend('migration.repository', function (DatabaseMigrationRepository $migrator, Application $app) {
            $migrations = $app['config']['database.migrations'];

            $table = is_array($migrations) ? ($migrations['table'] ?? null) : $migrations;

            return new SnapshotMigrationRepository($app['db'], $table, $app[ManagesVersions::class]);
        });

        $this->app->bind(Blueprint::class, function (Application $app, array $arguments) {
            return new SnapshotBlueprint(...$arguments);
        });

        $this->app->extend('migrator', function (Migrator $migrator, Application $app) {
            $versions = $app[ManagesVersions::class];
            $db = $app['db'];

            foreach ($app['config']->get('database.connections') as $name => $config) {
                $connections = $app['config']->get('database.connections');
                $connections[$name.'_snapshots'] = $config;
                $app['config']->set('database.connections', $connections);

                $db->extend($name.'_snapshots', fn () => SnapshotConnectionInitializer::initialize(
                    $app,
                    $db,
                    $versions,
                    $name
                ));
            }

            return new SnapshotMigrator(
                $app['migration.repository'],
                $db,
                $app['files'],
                $app['events'],
                $versions,
                $app
            );
        });

        return $this;
    }

    protected function registerGrammarMacros(): self
    {
        Grammar::macro('compileUnversionedForeign', app(UnversionedForeignKey::class)());
        Grammar::macro('compileDropUnversionedForeign', app(DropUnversionedForeignKey::class)());

        return $this;
    }

    protected function listenToEvents(): self
    {
        if ($migrator = config()->get('snapshots.auto_migrator')) {
            Event::listen(VersionCreated::class, $migrator);
        }

        if ($copier = config()->get('snapshots.copier')) {
            Event::listen(TableCreated::class, $copier);
        }

        if ($labeler = config()->get('snapshots.history.labeler')) {
            Event::listen(TableCopied::class, $labeler);
        }

        return $this;
    }
}
