<?php

namespace Plank\Snapshots\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

/**
 * @property (Authenticatable&Model)|null $user
 */
class VersionCreated
{
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public Authenticatable|null $user,
    ) {}
}
