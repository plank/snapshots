<?php

use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Repository\VersionRepository;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('The CopyTables listener correctly copies data', function () {
    beforeEach(function () {
        config()->set('snapshots.force_versions', true);

        app()->instance(ManagesVersions::class, new class extends VersionRepository
        {
            public function working(?Version $version): ?Version
            {
                if ($latest = $this->latest()) {
                    return $latest->getKey() !== $version?->getKey()
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

        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);
    });
});
