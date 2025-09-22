<?php

use Plank\Snapshots\Contracts\ManagesSnapshots;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Repository\SnapshotRepository;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('The CopyTables listener correctly copies data', function () {
    beforeEach(function () {
        config()->set('snapshots.force_snapshots', true);

        app()->instance(ManagesSnapshots::class, new class extends SnapshotRepository
        {
            public function working(?Snapshot $snapshot): ?Snapshot
            {
                if ($latest = $this->latest()) {
                    return $latest->getKey() !== $snapshot?->getKey()
                        ? $latest
                        : null;
                }

                return null;
            }
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('does not create versioned tables until a version is created', function () {
        assertDatabaseMissing('migrations', [
            'migration' => 'create_documents_table',
        ]);

        createFirstSnapshot('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);
    });
});
