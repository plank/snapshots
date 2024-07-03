<?php

use function Pest\Laravel\artisan;
use Plank\Snapshots\Tests\Models\Document;

describe('The Copier correctly copies data with Model Events', function () {
    beforeEach(function () {
        config()->set('snapshots.copier.model_events', true);

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
