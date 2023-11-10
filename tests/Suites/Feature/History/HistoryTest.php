<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Exceptions\CauserException;
use Plank\Snapshots\Exceptions\LabelingException;
use Plank\Snapshots\Listeners\LabelHistory;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Company;
use Plank\Snapshots\Tests\Models\Document;
use Plank\Snapshots\Tests\Models\Flag;
use Plank\Snapshots\Tests\Models\Image;

use function Pest\Laravel\artisan;

beforeEach(function () {
    config()->set('snapshots.history.observer', HistoryObserver::class);
    config()->set('snapshots.history.labler', LabelHistory::class);

    Event::listen(TableCopied::class, LabelHistory::class);
});

describe('Versioned Content has its History tracked correctly without Model Events', function () {
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

    it('tracks Create Operations correctly while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        Document::factory()->create();

        expect(History::query()->count())->toBe(1);

        /** @var History $item */
        $item = History::query()->first();

        expect($item->operation)->toBe(Operation::Created);
        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->count())->toBe(1);
    });

    it('tracks Update Operations correctly while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        $document = Document::factory()->create();
        $document->update(['title' => $document->title.' – Updated']);
        
        expect(History::query()->where('operation', Operation::Updated)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Updated)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->where('operation', Operation::Updated)->count())->toBe(1);
    });

    it('tracks Deleted Operations for models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        $document = Document::factory()->create();
        $document->delete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Deleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks Deleted Operations for SoftDeleting models which are force deleted while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        $flag = Flag::factory()->create();
        $flag->forceDelete();

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Deleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->where('operation', Operation::Deleted)->count())->toBe(1);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        $flag = Flag::factory()->create();
        $flag->delete();

        expect(History::query()->where('operation', Operation::SoftDeleted)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->where('operation', Operation::SoftDeleted)->count())->toBe(1);
    });

    it('tracks Restored Operations for SoftDeleting models while a version is active', function () {
        versions()->setActive(createFirstVersion('schema/create_for_model'));

        $flag = Flag::factory()->create();
        $flag->delete();
        $flag->restore();

        expect(History::query()->where('operation', Operation::Restored)->count())->toBe(1);

        /** @var History $item */
        $item = History::query()
            ->where('operation', Operation::Restored)
            ->first();

        expect((string) $item->version->number)->toBe('1.0.0');

        createPatchVersion('schema/create_for_model');

        expect(History::query()->where('operation', Operation::Restored)->count())->toBe(1);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models when a model is created as deleted', function () {
        $flag = Flag::factory()->create(['deleted_at' => now()]);

        expect (History::query()->where('operation', Operation::Created)->first())->toBeNull();

        $delated = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect($delated)->not->toBeNull();
        expect($delated->trackable_id)->toBe($flag->id);
    });

    it('tracks SoftDeleted Operations for SoftDeleting models when a model is updated with deleted timestamp set', function () {
        $flag = Flag::factory()->create();
        $flag->update(['deleted_at' => now()]);

        expect (History::query()->where('operation', Operation::Updated)->first())->toBeNull();

        $delated = History::query()
            ->where('operation', Operation::SoftDeleted)
            ->first();

        expect($delated)->not->toBeNull();
        expect($delated->trackable_id)->toBe($flag->id);
    });
});

describe('Versioned Content has its History tracked correctly with Model Events', function () {
    beforeEach(function () {
        config()->set('snapshots.copier.model_events', true);

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

describe('Unversioned Content has its History tracked correctly', function () {
    beforeEach(function () {
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

describe('History Labeling handles bad configuration and arguments', function () {
    it('exists early when no model is provided', function () {
        $labeler = new LabelHistory();

        expect($labeler->handle(new TableCopied('images', null, null)))->not->toThrow(LabelingException::class);
    });

    it('throws an exception when trying to label non-versioned models', function () {
        $labeler = new LabelHistory();

        $labeler->handle(new TableCopied('images', null, Image::class));
    })->throws(LabelingException::class);

    it('throws an exception when the causer does not implement the causer interface', function () {
        $badUser = new \Illuminate\Foundation\Auth\User;
        Auth::setUser($badUser);

        artisan('migrate', [
            '--path' => migrationPath('history'),
            '--realpath' => true,
        ])->run();

        Image::factory()->create();
    })->throws(CauserException::class);
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
