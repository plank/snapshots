<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Jobs\Copier;

/**
 * @property array<string,int> $tables
 */
class VersionMigrated
{
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public array $tables,
        public Authenticatable|null $causer,
    ) {}

    /**
     * @return Collection<Copier>
     */
    public function jobs(): Collection
    {
        /** @var class-string<Copier> $job */
        $job = config()->get('snapshots.release.copy.job');

        return Collection::make($this->tables)
            ->map(fn (string $table) => new $job($this->version, $table));
    }
}
