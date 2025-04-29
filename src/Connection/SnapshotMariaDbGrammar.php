<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Grammars\MariaDbGrammar;
use Plank\Snapshots\Concerns\HasUnversionedForeignKeys;

class SnapshotMariaDbGrammar extends MariaDbGrammar
{
    use HasUnversionedForeignKeys;
}
