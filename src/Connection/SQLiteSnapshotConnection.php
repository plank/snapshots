<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\SQLiteConnection;
use Plank\Snapshots\Concerns\HasVersionedConnection;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Schema\SQLiteSnapshotBuilder;

class SQLiteSnapshotConnection extends SQLiteConnection implements VersionedConnection
{
    use HasVersionedConnection;

    /**
     * @return SQLiteSnapshotBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SQLiteSnapshotBuilder($this, $this->versions, $this->tables);
    }
}
