<?php

namespace Plank\Snapshots\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as Orchestra;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\SnapshotServiceProvider;
use Plank\Snapshots\Tests\Database\Seeders\Model\UserSeeder;
use Plank\Snapshots\Tests\Models\User;

class TestCase extends Orchestra
{
    public ?VersionRepository $versions = null;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Plank\\Snapshots\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });

        $this->artisan('migrate', [
            '--path' => realpath(__DIR__.'/..').'/database/migrations',
            '--realpath' => true,
        ])->run();

        $this->artisan('migrate', [
            '--path' => realpath(__DIR__).'/Database/Migrations/base',
            '--realpath' => true,
        ])->run();

        $this->seed(UserSeeder::class);

        Auth::setUser(User::query()->where('name', 'Administrator')->first());

        $this->travelTo(Carbon::now());

        // Keep time ticking across database calls
        DB::listen(function () {
            $this->travel(1)->second();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            SnapshotServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('snapshots.history.observer', null);
    }
}
