<?php

namespace Plank\Snapshots\Exceptions;

class MigrationFormatException extends SnapshotsException
{
    public static function create(string $migration): self
    {
        return new self("The given {$migration} does not meet the format defined in the snapshots configuration file.");
    }
}
