<?php

namespace Plank\Snapshots\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

class VersionUnreleased
{
    use SerializesModels;

    public $version;

    public function __construct(Version&Model $version)
    {
        $this->version = $version;
    }
}
