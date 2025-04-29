<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\SqlServerGrammar;
use Plank\Snapshots\Concerns\HasUnversionedForeignKeys;

class SnapshotSqlServerGrammar extends SqlServerGrammar
{
    use HasUnversionedForeignKeys;
}
