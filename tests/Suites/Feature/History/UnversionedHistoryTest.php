<?php

use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Company;
use Plank\Snapshots\Tests\Models\Image;

use function Pest\Laravel\artisan;

describe('Unversioned Content has its History tracked correctly', function () {
    beforeEach(function () {
        config()->set('snapshots.observers.history', HistoryObserver::class);

        artisan('migrate', [
            '--path' => migrationPath('history'),
            '--realpath' => true,
        ])->run();
    });

    it('tracks Create Operations correctly', function () {
        Image::factory()->create();

        expect(History::query()->count())->toBe(1);
        expect($item = History::query()->first())->toBeInstanceOf(History::class);
        expect($item->operation)->toBe(Operation::Created);
    });

    it('tracks Update Operations correctly', function () {
        $image = Image::factory()->create();
        $image->src = $image->src.'?tracking=123';
        $image->save();

        expect(History::query()->where('operation', Operation::Updated)->count())->toBe(1);
    });

    it('tracks Deleted Operations for models', function () {
        $image = Image::factory()->create();
        $image->delete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks Deleted Operations for SoftDeleting models which are force deleted', function () {
        $company = Company::factory()->create();
        $company->forceDelete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models', function () {
        $company = Company::factory()->create();
        $company->delete();

        expect(History::query()->where('operation', Operation::SoftDeleted)->count())->toBe(1);
    });

    it('tracks Restored Operations for SoftDeleting models', function () {
        $company = Company::factory()->create();
        $company->delete();
        $company->restore();

        expect(History::query()->where('operation', Operation::Restored)->count())->toBe(1);
    });
});
