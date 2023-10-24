<?php

namespace Plank\Snapshots\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
    }
}
