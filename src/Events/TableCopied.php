<?php

namespace Plank\Snapshots\Events;

class TableCopied
{
    public function __construct(
        public string $table
    ) {}
}
