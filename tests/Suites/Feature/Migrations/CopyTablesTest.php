<?php

use function Pest\Laravel\artisan;
use Plank\Snapshots\Tests\Models\Document;

describe('The CopyTables listener correctly copies data', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('copies table data correctly for new versions', function () {
        $documents = Document::factory()->count(3)->create();

        versions()->setActive(createFirstVersion('schema/create'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');

        $documents->each(function (Document $document) {
            expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();
        });
    });
});
