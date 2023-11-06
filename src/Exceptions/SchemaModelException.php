<?php

namespace Plank\Snapshots\Exceptions;

class SchemaModelException extends SnapshotsException
{
    public static function create(string $table): self
    {
        return new self('You must use the `createForModel` method on the Snapshot schema builder in your migrations to use History Tracking. You are currently using the `create` method for table `'.$table.'`.');
    }
}
