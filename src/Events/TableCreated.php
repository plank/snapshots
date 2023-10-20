<?php

namespace Plank\Snapshots\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

class TableCreated
{
    use SerializesModels;

    /**
     * @param class-string<Model>|null  $model
     */
    public function __construct(
        public string $table,
        public ?Version $version,
        public ?string $model = null,
    ) {
    }
}
