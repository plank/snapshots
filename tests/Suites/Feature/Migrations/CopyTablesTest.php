<?php

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\DataCopied;
use Plank\Snapshots\Tests\Models\Document;

use function Pest\Laravel\artisan;

describe('The CopyTables listener correctly copies data', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('copies table data correctly for new versions', function () {
        Event::fake([DataCopied::class]);

        $documents = Document::factory()->count(3)->create();

        snapshots()->setActive($snapshot = createFirstSnapshot('schema/create'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');

        $documents->each(function (Document $document) {
            expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();
        });

        $snapshot->refresh();
        expect($snapshot->migrated)->toBeTrue();
        expect($snapshot->copied)->toBeTrue();

        Event::assertDispatchedTimes(DataCopied::class, 1);
    });
});
