<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class SchemaModelException extends SnapshotsException
{
    public static function create(string $table): self
    {
        return new self('You must use the `createForModel` method on the Snapshot schema builder to use the CopyModels auto_copier. You are currently using the `create` method for table `'.$table.'`.');
    }
}
