<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\MySqlConnection;
use Plank\Snapshots\Concerns\HasVersionedConnection;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Schema\MySqlSnapshotBuilder;

class MySqlSnapshotConnection extends MySqlConnection implements VersionedConnection
{
    use HasVersionedConnection;

    /**
     * @return MySqlSnapshotBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MySqlSnapshotBuilder($this, $this->versions, $this->tables);
    }
}
