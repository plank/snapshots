<?php

namespace Plank\Snapshots\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Plank\Snapshots\Contracts\Version;

class TableCopied
{
    use SerializesModels;

    public function __construct(
        public string $table,
        public (Version&Model)|null $version,
        public ?string $model = null,
    ) {
    }

    public static function fromCreated(TableCreated $event): self
    {
        return new static(
            table: $event->table,
            version: $event->version,
            model: $event->model,
        );
    }
}
