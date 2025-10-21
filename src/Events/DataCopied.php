<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

/**
 * @property array<string,int> $tables
 * @property (Authenticatable&Model)|null $user
 */
class DataCopied
{
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public ?Authenticatable $user,
    ) {}
}
