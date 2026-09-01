<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\Grammar;
use Plank\Snapshots\Concerns\HasPlainForeignKeys;

class SnapshotGrammar extends Grammar
{
    use HasPlainForeignKeys;
}
