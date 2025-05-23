<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\Grammar;
use Plank\Snapshots\Concerns\HasUnversionedForeignKeys;

class SnapshotGrammar extends Grammar
{
    use HasUnversionedForeignKeys;
}
