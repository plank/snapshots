<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Repository\CauserRepository;

class CauserException extends SnapshotsException
{
    public static function create(): self
    {
        return new self('To use "'.CauserRepository::class.'", you must ensure all Authenticatable models implement "'.CausesChanges::class.'". Otherwise you can configure your own implementation.');
    }
}
