<?php

namespace Plank\Snapshots\Tests;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase as Orchestra;
use Plank\Snapshots\Repository\VersionRepository;
use Plank\Snapshots\SnapshotServiceProvider;

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
