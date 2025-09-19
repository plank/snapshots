<?php

use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Document;
use Plank\Snapshots\Tests\Models\Flag;

use function Pest\Laravel\artisan;

describe('Versioned Content has its History tracked correctly when copying by table', function () {
    beforeEach(function () {
        config()->set('snapshots.observers.history', HistoryObserver::class);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('tracks Create Operations correctly', function () {
        Document::factory()->create();

        expect(History::query()->count())->toBe(1);

        /** @var History $item */
        $item = History::query()->first();

        expect($item->operation)->toBe(Operation::Created);
        expect($item->version)->toBeNull();

        createFirstVersion('schema/create');

        expect(History::query()->count())->toBe(2);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Created)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Snapshotted)
            ->first();

        expect($item->version)->toBeNull();

        Document::factory()->create();
        versions()->setActive(createMinorVersion('schema/create'));

        // Now we have 1 created history items and 2 snapshotted history items for the first
        // document, and 1 created history item and 1 snapshotted history item for the second document.
        expect(History::query()->where('operation', Operation::Created)->count())->toBe(2);
        expect(History::query()->where('operation', Operation::Snapshotted)->count())->toBe(3);
    });

    it('tracks Update Operations correctly', function () {
        $document = Document::factory()->create();
        expect(History::query()->count())->toBe(1);

        $document->update(['title' => $document->title.' – Updated']);
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create');

        $created = History::query()
            ->where('operation', Operation::Created)
            ->get();

        expect($created)->toHaveCount(1);
        expect($created = $created->first())->toBeInstanceOf(History::class);
        expect((string) $created->version->number)->toBe('1.0.0');
        expect($created->trackable_id)->toBe($document->id);

        $updated = History::query()
            ->where('operation', Operation::Updated)
            ->get();

        expect($updated)->toHaveCount(1);
        expect($updated = $updated->first())->toBeInstanceOf(History::class);
        expect((string) $updated->version->number)->toBe('1.0.0');
        expect($updated->trackable_id)->toBe($document->id);

        $snapshotted = History::query()
            ->where('operation', Operation::Snapshotted)
            ->get();

        expect($snapshotted)->toHaveCount(1);
        expect($snapshotted = $snapshotted->first())->toBeInstanceOf(History::class);
        expect($snapshotted->version)->toBeNull();
        expect($snapshotted->trackable_id)->toBe($document->id);
    });

    it('tracks Deleted Operations for models', function () {
        $document = Document::factory()->create();
        expect(History::query()->count())->toBe(1);

        $document->delete();
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create');

        $created = History::query()
            ->where('operation', Operation::Created)
            ->get();

        expect($created)->toHaveCount(1);
        expect($created = $created->first())->toBeInstanceOf(History::class);
        expect((string) $created->version->number)->toBe('1.0.0');
        expect($created->trackable_id)->toBe($document->id);

        $deleted = History::query()
            ->where('operation', Operation::Deleted)
            ->get();

        expect($deleted)->toHaveCount(1);
        expect($deleted = $deleted->first())->toBeInstanceOf(History::class);
        expect((string) $deleted->version->number)->toBe('1.0.0');
        expect($deleted->trackable_id)->toBe($document->id);

        $snapshotted = History::query()
            ->where('operation', Operation::Snapshotted)
            ->get();

        expect($snapshotted)->toHaveCount(0);
    });

    it('tracks Deleted Operations for SoftDeleting models which are force deleted', function () {
        $flag = Flag::factory()->create();
        expect(History::query()->count())->toBe(1);

        $flag->forceDelete();
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create');

        $created = History::query()
            ->where('operation', Operation::Created)
            ->get();

        expect($created)->toHaveCount(1);
        expect($created = $created->first())->toBeInstanceOf(History::class);
        expect((string) $created->version->number)->toBe('1.0.0');
        expect($created->trackable_id)->toBe($flag->id);

        $deleted = History::query()
            ->where('operation', Operation::Deleted)
            ->get();

        expect($deleted)->toHaveCount(1);
        expect($deleted = $deleted->first())->toBeInstanceOf(History::class);
        expect((string) $deleted->version->number)->toBe('1.0.0');
        expect($deleted->trackable_id)->toBe($flag->id);

        $snapshotted = History::query()
            ->where('operation', Operation::Snapshotted)
            ->get();

        expect($snapshotted)->toHaveCount(0);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models', function () {
        $flag = Flag::factory()->create();
        expect(History::query()->count())->toBe(1);

        $flag->delete();
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create');

        $created = History::query()
            ->where('operation', Operation::Created)
            ->get();

        expect($created)->toHaveCount(1);
        expect($created = $created->first())->toBeInstanceOf(History::class);
        expect((string) $created->version->number)->toBe('1.0.0');
        expect($created->trackable_id)->toBe($flag->id);

        $deleted = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->get();

        expect($deleted)->toHaveCount(1);
        expect($deleted = $deleted->first())->toBeInstanceOf(History::class);
        expect((string) $deleted->version->number)->toBe('1.0.0');
        expect($deleted->trackable_id)->toBe($flag->id);

        $snapshotted = History::query()
            ->where('operation', Operation::Snapshotted)
            ->get();

        expect($snapshotted)->toHaveCount(1);
        expect($snapshotted = $snapshotted->first())->toBeInstanceOf(History::class);
        expect($snapshotted->version)->toBeNull();
        expect($snapshotted->trackable_id)->toBe($flag->id);
    });

    it('tracks Restored Operations for SoftDeleting models', function () {
        $flag = Flag::factory()->create();
        expect(History::query()->count())->toBe(1);

        $flag->delete();
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create');

        $flag->restore();
        expect(History::query()->count())->toBe(4);

        $created = History::query()
            ->where('operation', Operation::Created)
            ->get();

        expect($created)->toHaveCount(1);
        expect($created = $created->first())->toBeInstanceOf(History::class);
        expect((string) $created->version->number)->toBe('1.0.0');
        expect($created->trackable_id)->toBe($flag->id);

        $deleted = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->get();

        expect($deleted)->toHaveCount(1);
        expect($deleted = $deleted->first())->toBeInstanceOf(History::class);
        expect((string) $deleted->version->number)->toBe('1.0.0');
        expect($deleted->trackable_id)->toBe($flag->id);

        $snapshotted = History::query()
            ->where('operation', Operation::Snapshotted)
            ->get();

        expect($snapshotted)->toHaveCount(1);
        expect($snapshotted = $snapshotted->first())->toBeInstanceOf(History::class);
        expect($snapshotted->version)->toBeNull();
        expect($snapshotted->trackable_id)->toBe($flag->id);

        $restored = History::query()
            ->where('operation', Operation::Restored)
            ->get();

        expect($restored)->toHaveCount(1);
        expect($restored = $restored->first())->toBeInstanceOf(History::class);
        expect($restored->version)->toBeNull();
        expect($restored->trackable_id)->toBe($flag->id);
        expect($restored->causer->email)->toBe('admin@app.test');
    });

    it('tracks Create Operations correctly while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        Document::factory()->create();

        expect(History::query()->count())->toBe(1);

        /** @var History $item */
        $item = History::query()->first();

        expect($item->operation)->toBe(Operation::Created);
        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->count())->toBe(1);
    });

    it('tracks Update Operations correctly while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $document = Document::factory()->create();
        $document->update(['title' => $document->title.' – Updated']);

        expect(History::query()->where('operation', Operation::Updated)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Updated)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->where('operation', Operation::Updated)->count())->toBe(1);
    });

    it('tracks Deleted Operations for models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $document = Document::factory()->create();
        $document->delete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Deleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks Deleted Operations for SoftDeleting models which are force deleted while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $flag = Flag::factory()->create();
        $flag->forceDelete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Deleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $flag = Flag::factory()->create();
        $flag->delete();

        expect(History::query()->where('operation', Operation::SoftDeleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->where('operation', Operation::SoftDeleted)->count())->toBe(1);
    });

    it('tracks Restored Operations for SoftDeleting models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $flag = Flag::factory()->create();
        $flag->delete();
        $flag->restore();

        expect(History::query()->where('operation', Operation::Restored)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Restored)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create');

        expect(History::query()->where('operation', Operation::Restored)->count())->toBe(1);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models when a model is created as deleted', function () {
        $flag = Flag::factory()->create(['deleted_at' => now()]);

        expect(History::query()->where('operation', Operation::Created)->first())->toBeNull();

        $delated = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect($delated)->not->toBeNull();
        expect($delated->trackable_id)->toBe($flag->id);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models when a model is updated with deleted timestamp set', function () {
        $flag = Flag::factory()->create();
        $flag->update(['deleted_at' => now()]);

        expect(History::query()->where('operation', Operation::Updated)->first())->toBeNull();

        $delated = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect($delated)->not->toBeNull();
        expect($delated->trackable_id)->toBe($flag->id);
    });
});
