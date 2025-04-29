<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Plank\Snapshots\Concerns\HasUnversionedForeignKeys;

class SnapshotMySqlGrammar extends MySqlGrammar
{
    use HasUnversionedForeignKeys;
}
