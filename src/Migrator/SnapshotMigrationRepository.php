<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Plank\Snapshots\Contracts\ManagesVersions;

class SnapshotMigrationRepository extends DatabaseMigrationRepository
{
    protected ManagesVersions $versions;

    /**
     * Create a new database migration repository instance.
     *
     * @param  string  $table
     * @return void
     */
    public function __construct(
        Resolver $resolver,
        $table,
        ManagesVersions $versions
    ) {
        $this->versions = $versions;

        parent::__construct($resolver, $table);
    }

    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int  $batch
     * @return void
     */
    public function log($file, $batch)
    {
        if ($active = $this->versions->active()) {
            $file = $active->key()->prefix($file);
        }

        parent::log($file, $batch);
    }
}
