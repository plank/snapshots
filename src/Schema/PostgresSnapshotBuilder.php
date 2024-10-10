<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\PostgresBuilder;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

class PostgresSnapshotBuilder extends PostgresBuilder implements VersionedSchema
{
    use HasVersionedSchema;
}
