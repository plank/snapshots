<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\ConnectionInterface;

/**
 * @property ManagesVersions $versions
 * @property ManagesCreatedTables $ables
 * @property VersionedSchema|null $schemaBuilder
 */
interface VersionedConnection extends ConnectionInterface
{
    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\Builder&\Plank\Snapshots\Contracts\VersionedSchema
     */
    public function getSchemaBuilder();
}
