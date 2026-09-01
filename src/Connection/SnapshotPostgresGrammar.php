<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Plank\Snapshots\Concerns\HasPlainForeignKeys;

class SnapshotPostgresGrammar extends PostgresGrammar
{
    use HasPlainForeignKeys;
}
