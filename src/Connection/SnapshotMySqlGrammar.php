<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Plank\Snapshots\Concerns\HasPlainForeignKeys;

class SnapshotMySqlGrammar extends MySqlGrammar
{
    use HasPlainForeignKeys;
}
