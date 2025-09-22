<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Jobs\Copier;
use Plank\Snapshots\Jobs\CopyTable;
use Plank\Snapshots\Jobs\MarkAsCopied;

/**
 * @property array<string,int> $tables
 */
class SnapshotMigrated
{
    use SerializesModels;

    public function __construct(
        public Snapshot&Model $snapshot,
        public array $tables,
        public (Authenticatable&Model)|null $user,
    ) {}

    /**
     * @return Collection<Copier>
     */
    public function jobs(): Collection
    {
        return Collection::make($this->tables)
            ->map(fn (string $table) => new CopyTable($this->snapshot, $table))
            ->push(new MarkAsCopied($this->snapshot, $this->user));
    }
}
