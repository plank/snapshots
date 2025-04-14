<?php

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\DataCopied;
use Plank\Snapshots\Jobs\CopyModel;
use Plank\Snapshots\Tests\Models\Document;
use Plank\Snapshots\Tests\Models\Signature;

use function Pest\Laravel\artisan;

describe('The Copier correctly copies data with Model Events', function () {
    beforeEach(function () {
        config()->set('snapshots.release.copy.job', CopyModel::class);

        artisan('migrate', [
            '--path' => migrationPath('model_pivot'),
            '--realpath' => true,
        ])->run();
    });

    it('copies model data correctly for new versions', function () {
        Event::fake([DataCopied::class]);

        $documents = Document::factory()->count(3)->create();

        versions()->setActive($version = createFirstVersion('model_pivot'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');

        $documents->each(function (Document $document) {
            expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();
        });

        $version->refresh();
        expect($version->migrated)->toBeTrue();
        expect($version->copied)->toBeTrue();

        Event::assertDispatchedTimes(DataCopied::class, 1);
    });

    it('copies pivot tables correctly for new versions using the model copier', function () {
        $document = Document::factory()->create();
        $signatures = Signature::factory()->count(3)->create();
        $document->signatures()->attach($signatures->pluck('id'));

        versions()->setActive(createFirstVersion('model_pivot'));

        expect((new Document)->getTable())->toBe('v1_0_0_documents');
        expect((new Signature)->getTable())->toBe('v1_0_0_signatures');
        expect($document->signatures()->newPivot()->getTable())->toBe('v1_0_0_document_signature');

        expect(Document::query()->whereKey($document->id)->exists())->toBeTrue();

        $signatures->each(function (Signature $signature) {
            expect(Signature::query()->whereKey($signature->id)->exists())->toBeTrue();
        });

        expect($document->signatures()->count())->toBe(3);
    });
});
