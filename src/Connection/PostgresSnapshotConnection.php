<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\PostgresConnection;
use Plank\Snapshots\Concerns\HasVersionedConnection;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Schema\PostgresSnapshotBuilder;

class PostgresSnapshotConnection extends PostgresConnection implements VersionedConnection
{
    use HasVersionedConnection;

    /**
     * @return PostgresSnapshotBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new PostgresSnapshotBuilder($this, $this->versions, $this->tables);
    }
}
