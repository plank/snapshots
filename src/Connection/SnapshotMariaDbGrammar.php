<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\MariaDbGrammar;
use Plank\Snapshots\Concerns\HasPlainForeignKeys;

class SnapshotMariaDbGrammar extends MariaDbGrammar
{
    use HasPlainForeignKeys;
}
