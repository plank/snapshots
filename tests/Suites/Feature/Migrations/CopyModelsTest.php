<?php

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Listeners\CopyModels;
use Plank\Snapshots\Tests\Models\Document;

use function Pest\Laravel\artisan;

describe('The CopyModels listener correctly copies data', function () {
    beforeEach(function () {
        Event::forget(TableCreated::class);
        Event::listen(TableCreated::class, CopyModels::class);

        artisan('migrate', [
            '--path' => migrationPath('schema/create_for_model'),
            '--realpath' => true,
        ])->run();
    });

    it('copies model data correctly for new versions', function () {
        $documents = Document::factory()->count(3)->create();

        versions()->setActive(createFirstVersion('schema/create_for_model'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');

        $documents->each(function (Document $document) {
            expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();
        });
    });
});
