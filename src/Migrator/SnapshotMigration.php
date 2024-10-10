<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Facades\SnapshotSchema;

abstract class SnapshotMigration extends Migration
{
    public Builder&VersionedSchema $schema;

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
