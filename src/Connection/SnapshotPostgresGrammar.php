<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Plank\Snapshots\Concerns\HasUnversionedForeignKeys;

class SnapshotPostgresGrammar extends PostgresGrammar
{
    use HasUnversionedForeignKeys;
}
