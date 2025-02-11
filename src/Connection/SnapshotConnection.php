<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Connection;
use Plank\Snapshots\Concerns\HasVersionedConnection;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Schema\SnapshotBuilder;

class SnapshotConnection extends Connection implements VersionedConnection
{
    use HasVersionedConnection;

    /**
     * @return SnapshotBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SnapshotBuilder($this, $this->versions, $this->tables);
    }
}
