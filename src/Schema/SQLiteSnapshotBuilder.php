<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\SQLiteBuilder;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

class SQLiteSnapshotBuilder extends SQLiteBuilder implements VersionedSchema
{
    use HasVersionedSchema;
}
