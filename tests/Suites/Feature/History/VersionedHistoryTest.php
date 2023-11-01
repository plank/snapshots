<?php

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Listeners\CopyModels;
use Plank\Snapshots\Listeners\LabelHistory;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Document;
use Plank\Snapshots\Tests\Models\Flag;

use function Pest\Laravel\artisan;

beforeEach(function () {
    config()->set('snapshots.history.observer', HistoryObserver::class);
    config()->set('snapshots.history.labler', LabelHistory::class);

    Event::listen(TableCopied::class, LabelHistory::class);
});

describe('Versioned Content has its History tracked correctly for CopyTable auto_copier', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create_for_model'),
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

        createFirstVersion('schema/create_for_model');

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
        versions()->setActive(createMinorVersion('schema/create_for_model'));

        // Now we have 2 created history items, and 2 snapshotted history items for the first
        // document, and 1 snapshotted history item for the second document.
        expect(History::query()->where('operation', Operation::Created)->count())->toBe(2);
        expect(History::query()->where('operation', Operation::Snapshotted)->count())->toBe(3);
    });

    it('tracks Update Operations correctly', function () {
        $document = Document::factory()->create();
        expect(History::query()->count())->toBe(1);

        $document->update(['title' => $document->title.' – Updated']);
        expect(History::query()->count())->toBe(2);

        createFirstVersion('schema/create_for_model');

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

        createFirstVersion('schema/create_for_model');

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

        createFirstVersion('schema/create_for_model');

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

        createFirstVersion('schema/create_for_model');

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

        createFirstVersion('schema/create_for_model');

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
});

describe('Versioned Content has its History tracked correctly for CopyModels auto_copier', function () {
    beforeEach(function () {
        Event::forget(TableCreated::class);
        Event::listen(TableCreated::class, CopyModels::class);

        artisan('migrate', [
            '--path' => migrationPath('schema/create_for_model'),
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

        createFirstVersion('schema/create_for_model');

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
        versions()->setActive(createMinorVersion('schema/create_for_model'));

        // Now we have 2 created history items, and 2 snapshotted history items for the first
        // document, and 1 snapshotted history item for the second document.
        expect(History::query()->where('operation', Operation::Created)->count())->toBe(2);
        expect(History::query()->where('operation', Operation::Snapshotted)->count())->toBe(3);
    });
});
