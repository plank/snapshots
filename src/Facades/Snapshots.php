<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\ManagesSnapshots;
use Plank\Snapshots\Contracts\Snapshot;

/**
 * @method static void setActive(?Snapshot $snapshot)
 * @method static void clearActive()
 * @method static mixed withSnapshotActive(string|VersionNumber|Snapshot|null $snapshot, callable $callback)
 * @method static Snapshot|null active()
 * @method static Snapshot|null latest()
 * @method static Snapshot|null working(Snapshot|null $snapshot)
 * @method static Snapshot|null find($key)
 * @method static Snapshot|null byNumber(string $number)
 * @method static Collection all()
 */
class Snapshots extends Facade
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
        return ManagesSnapshots::class;
    }
}
