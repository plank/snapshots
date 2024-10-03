<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\Builder;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

/**
 * @mixin Builder
 */
class SnapshotSchemaBuilder extends Builder implements VersionedSchema
{
    use HasVersionedSchema;
}
