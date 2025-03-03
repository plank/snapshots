<?php

use Illuminate\Support\Facades\Auth;
use Plank\Snapshots\Exceptions\CauserException;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Image;

use function Pest\Laravel\artisan;

describe('History Labeling handles bad configuration and arguments', function () {
    it('throws an exception when the causer does not implement the causer interface', function () {
        config()->set('snapshots.observers.history', HistoryObserver::class);

        $badUser = new \Illuminate\Foundation\Auth\User;
        Auth::setUser($badUser);

        artisan('migrate', [
            '--path' => migrationPath('history'),
            '--realpath' => true,
        ])->run();

        Image::factory()->create();
    })->throws(CauserException::class);
});
