<?php

use Plank\Snapshots\Jobs\CopyModel;
use Plank\Snapshots\Tests\Models\Document;

use function Pest\Laravel\artisan;

describe('The Copier correctly copies data with Model Events', function () {
    beforeEach(function () {
        config()->set('snapshots.release.copy.job', CopyModel::class);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('copies model data correctly for new versions', function () {
        $documents = Document::factory()->count(3)->create();

        versions()->setActive(createFirstVersion('schema/create'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');

        $documents->each(function (Document $document) {
            expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();
        });
    });
});
