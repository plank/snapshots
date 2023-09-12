<?php

namespace Plank\Snapshots\Events;

use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

class TableCreated
{
    use SerializesModels;

    public function __construct(
        public string $table,
        public ?Version $version,
    ) {
    }
}
