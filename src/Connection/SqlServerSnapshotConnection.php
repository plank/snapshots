<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\SqlServerConnection;
use Plank\Snapshots\Concerns\HasVersionedConnection;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Schema\SqlServerSnapshotBuilder;

class SqlServerSnapshotConnection extends SqlServerConnection implements VersionedConnection
{
    use HasVersionedConnection;

    /**
     * @return SqlServerSnapshotBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SqlServerSnapshotBuilder($this, $this->versions, $this->tables);
    }
}
