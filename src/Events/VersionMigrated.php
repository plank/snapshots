<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Jobs\Copier;
use Plank\Snapshots\Jobs\CopyTable;
use Plank\Snapshots\Jobs\MarkAsCopied;

/**
 * @property array<string,int> $tables
 */
class VersionMigrated
{
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public array $tables,
        public (Authenticatable&Model)|null $user,
    ) {}

    /**
     * @return Collection<Copier>
     */
    public function jobs(): Collection
    {
        return Collection::make($this->tables)
            ->map(fn (string $table) => new CopyTable($this->version, $table))
            ->push(new MarkAsCopied($this));
    }
}
