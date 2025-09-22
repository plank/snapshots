<?php

use Plank\Snapshots\Models\Existence;
use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Tests\Models\Document;
use Plank\Snapshots\Tests\Models\Flag;

use function Pest\Laravel\artisan;

describe('Versioned Content has its Existence tracked correctly when copying by table', function () {
    beforeEach(function () {
        config()->set('snapshots.observers.history', ExistenceObserver::class);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('tracks creation correctly', function () {
        Document::factory()->create();

        expect(Existence::query()->count())->toBe(1);

        $existence = Existence::query()->latest()->first();
        expect($existence->snapshot)->toBeNull();

        createFirstSnapshot('schema/create');

        expect(Existence::query()->count())->toBe(2);

        $existence = Existence::query()->latest()->first();
        expect((string) $existence->snapshot->number)->toBe('1.0.0');

        snapshots()->setActive(createMinorSnapshot('schema/create'));
        Document::factory()->create();

        $existence = Existence::query()->latest()->first();
        expect((string) $existence->snapshot->number)->toBe('1.1.0');
    });

    it('tracks deletions correctly', function () {
        $document = Document::factory()->create();
        expect(Existence::query()->count())->toBe(1);

        $document->delete();
        expect(Existence::query()->count())->toBe(0);

        $document = Document::factory()->create();

        snapshots()->setActive(createFirstSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(2);

        snapshots()->setActive(createMinorSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(3);

        $document->delete();

        expect(Existence::query()->count())->toBe(2);
    });

    it('tracks deletions correctly for SoftDeleting models which are force deleted', function () {
        $flag = Flag::factory()->create();
        expect(Existence::query()->count())->toBe(1);

        $flag->forceDelete();
        expect(Existence::query()->count())->toBe(0);

        $flag = Flag::factory()->create();

        snapshots()->setActive(createFirstSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(2);

        snapshots()->setActive(createMinorSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(3);

        $flag->forceDelete();

        expect(Existence::query()->count())->toBe(2);
    });

    it('tracks deletions correctly for SoftDeleting models which are soft deleted', function () {
        $flag = Flag::factory()->create();
        expect(Existence::query()->count())->toBe(1);

        $flag->delete();
        expect(Existence::query()->count())->toBe(0);

        $flag = Flag::factory()->create();

        snapshots()->setActive(createFirstSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(2);

        snapshots()->setActive(createMinorSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(3);

        $flag->delete();

        expect(Existence::query()->count())->toBe(2);
    });

    it('tracks restoration correctly for SoftDeleting models', function () {
        $flag = Flag::factory()->create();
        expect(Existence::query()->count())->toBe(1);

        $flag->delete();
        expect(Existence::query()->count())->toBe(0);

        $flag = Flag::factory()->create();

        snapshots()->setActive(createFirstSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(2);

        snapshots()->setActive(createMinorSnapshot('schema/create'));

        expect(Existence::query()->count())->toBe(3);

        $flag->delete();

        expect(Existence::query()->count())->toBe(2);

        $flag->restore();

        expect(Existence::query()->count())->toBe(3);
    });
});
