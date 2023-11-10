<?php

namespace Plank\Snapshots\Contracts;

use Plank\Snapshots\Events\TableCreated;

interface ManagesCreatedTables
{
    /**
     * Queue a Created Table
     */
    public function queue(TableCreated $event): void;

    /**
     * Dequeue a Created Table
     */
    public function dequeue(): ?TableCreated;

    /**
     * Flush all of the stored Created Tables
     */
    public function flush(): int;
}
