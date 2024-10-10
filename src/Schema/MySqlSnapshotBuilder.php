<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\MySqlBuilder;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

class MySqlSnapshotBuilder extends MySqlBuilder implements VersionedSchema
{
    use HasVersionedSchema;
}
