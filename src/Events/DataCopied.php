<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Snapshot;

/**
 * @property array<string,int> $tables
 */
class DataCopied
{
    use SerializesModels;

    public function __construct(
        public Snapshot&Model $snapshot,
        public (Authenticatable&Model)|null $user,
    ) {}
}
