<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\SqlServerBuilder;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

class SqlServerSnapshotBuilder extends SqlServerBuilder implements VersionedSchema
{
    use HasVersionedSchema;
}
