<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Migrations\Migration;
use Plank\Snapshots\Facades\SnapshotSchema;

abstract class SnapshotMigration extends Migration
{
    public SnapshotSchemaBuilder $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = SnapshotSchema::getFacadeRoot();
    }
}
