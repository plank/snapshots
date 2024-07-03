<?php

use Illuminate\Support\Facades\File;
use function Pest\Laravel\artisan;

describe('The install command properly installs the package.', function () {
    afterEach(function () {
        if (File::exists(config_path('snapshots.php'))) {
            File::delete(config_path('snapshots.php'));
        }

        foreach (File::allFiles(database_path('migrations')) as $file) {
            File::delete($file->getPathname());
        }
    });

    it('publishes the config file when confirmed', function () {
        File::delete(config_path('snapshots.php'));
        expect(file_exists(config_path('snapshots.php')))->toBeFalse();

        artisan('snapshots:install')
            ->expectsConfirmation('Would you like to publish the config file?', 'yes')
            ->expectsConfirmation('Would you like to publish the migrations?', 'no')
            ->expectsConfirmation('Would you like to run the migrations now?', 'no')
            ->assertExitCode(0);

        expect(file_exists(config_path('snapshots.php')))->toBeTrue();
    });

    it('publishes the config file when not confirmed', function () {
        File::delete(config_path('snapshots.php'));
        expect(file_exists(config_path('snapshots.php')))->toBeFalse();

        artisan('snapshots:install')
            ->expectsConfirmation('Would you like to publish the config file?', 'no')
            ->expectsConfirmation('Would you like to publish the migrations?', 'no')
            ->expectsConfirmation('Would you like to run the migrations now?', 'no')
            ->assertExitCode(0);

        expect(file_exists(config_path('snapshots.php')))->toBeFalse();
    });

    it('publishes the migrations file when not confirmed', function () {
        $now = now();

        $migrationsPath = database_path('migrations');
        $migrations = File::allFiles($migrationsPath);
        $migration = null;

        if (count($migrations) > 0) {
            $migration = $migrations[0]->getPathname();
        } else {
            $migration = $migrationsPath.'/'.$now->addSeconds(2)->format('Y_m_d_His').'_create_versions_table.php';
        }

        File::delete($migration);
        expect(File::allFiles($migrationsPath))->toBeEmpty();

        artisan('snapshots:install')
            ->expectsConfirmation('Would you like to publish the config file?', 'no')
            ->expectsConfirmation('Would you like to publish the migrations?', 'yes')
            ->expectsConfirmation('Would you like to run the migrations now?', 'no')
            ->assertExitCode(0);

        expect(file_exists($migration))->toBeTrue();
        File::delete($migration);
    });

    it('doesnt publish the migrations file when not confirmed', function () {
        $now = now();

        $migrationsPath = database_path('migrations');
        $migrations = File::allFiles($migrationsPath);
        $migration = null;

        if (count($migrations) > 0) {
            $migration = $migrations[0]->getPathname();
        } else {
            $migration = $migrationsPath.'/'.$now->addSecond()->format('Y_m_d_His').'_create_versions_table.php';
        }

        File::delete($migration);
        expect(File::allFiles($migrationsPath))->toBeEmpty();

        artisan('snapshots:install')
            ->expectsConfirmation('Would you like to publish the config file?', 'no')
            ->expectsConfirmation('Would you like to publish the migrations?', 'no')
            ->expectsConfirmation('Would you like to run the migrations now?', 'no')
            ->assertExitCode(0);

        expect(file_exists($migration))->toBeFalse();
    });
});
