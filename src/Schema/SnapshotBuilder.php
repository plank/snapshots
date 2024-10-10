<?php

namespace Plank\Snapshots\Schema;

use Illuminate\Database\Schema\Builder;
use Nette\NotImplementedException;
use Plank\Snapshots\Concerns\HasVersionedSchema;
use Plank\Snapshots\Contracts\VersionedSchema;

/**
 * @mixin Builder
 */
class SnapshotBuilder extends Builder implements VersionedSchema
{
    use HasVersionedSchema;

    /**
     * Get the indexes for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getIndexes($table)
    {
        throw new NotImplementedException('The `getIndexes` method requires a grammar specific Schema Builder.');
    }

    /**
     * Get the foreign keys for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getForeignKeys($table)
    {
        throw new NotImplementedException('The `getForeignKeys` method requires a grammar specific Schema Builder.');
    }
}
