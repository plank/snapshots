<?php

namespace Plank\Snapshots\Repository;

use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Events\TableCreated;

class TableRepository implements ManagesCreatedTables
{
    /** @var array<TableCreated> */
    protected array $tables = [];

    public function queue(TableCreated $event): void
    {
        $this->tables[$event->table] = $event;
    }

    public function dequeue(): ?TableCreated
    {
        return array_shift($this->tables);
    }

    public function all(): array
    {
        return $this->tables;
    }

    public function flush(): int
    {
        $count = count($this->tables);
        $this->tables = [];

        return $count;
    }
}
