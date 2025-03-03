<?php

use Plank\Snapshots\Jobs\CopyTable;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Company;
use Plank\Snapshots\Tests\Models\Image;

use function Pest\Laravel\artisan;

beforeEach(function () {
    config()->set('snapshots.observers.history', HistoryObserver::class);
    config()->set('snapshots.release.copy.job', CopyTable::class);
});

describe('Trackable models have a correct hidden property', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('history'),
            '--realpath' => true,
        ])->run();
    });

    it('shows the hidden attribute correctly for non-soft-deleting models', function () {
        $image = Image::factory()->create();

        expect($image->hidden)->toBeFalse();

        $image->delete();

        expect($image->hidden)->toBeTrue();
    });

    it('shows the hidden attribute correctly for soft-deleting models', function () {
        $company = Company::factory()->create();

        expect($company->hidden)->toBeFalse();

        $company->delete();

        expect($company->hidden)->toBeTrue();
    });
});

describe('Trackable models do not log hidden model attributes', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('history'),
            '--realpath' => true,
        ])->run();
    });

    it('does not log hidden attributes', function () {
        Company::factory()->create();

        expect($item = History::query()->first())->toBeInstanceOf(History::class);
        expect($item->to)->not->toHaveKey('secret');
    });
});
