<?php

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesCreatedTables;

use function Pest\Laravel\artisan;

describe('The Table Repository Gathers TableCreated events correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();
    });

    it('gathers tables correctly', function () {
        Event::forget(MigrationsEnded::class);

        Event::listen(MigrationsEnded::class, function () {
            expect(app(ManagesCreatedTables::class)->all())
                ->toHaveKeys([
                    'posts',
                    'post_post',
                    'seos',
                    'taggables',
                ]);
        });

        versions()->setActive(createFirstVersion('query'));
    });
});
