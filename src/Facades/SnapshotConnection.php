<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Factory\SnapshotConnectionBuilder;

/**
 * @method static VersionedConnection default()
 * @method static VersionedConnection from(Connection $connection)
 */
class SnapshotConnection extends Facade
{
    /**
     * Indicates if the resolved facade should be cached.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SnapshotConnectionBuilder::class;
    }
}
