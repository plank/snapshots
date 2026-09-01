<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Plank\Snapshots\Facades\Snapshots;

class SnapshotMigrationRepository extends DatabaseMigrationRepository
{
    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int  $batch
     * @return void
     */
    public function log($file, $batch)
    {
        if ($active = Snapshots::active()) {
            $file = $active->key()->prefix($file);
        }

        parent::log($file, $batch);
    }
}
