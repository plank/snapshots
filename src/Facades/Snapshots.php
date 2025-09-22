<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\ManagesSnapshots;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\ValueObjects\VersionNumber;

/**
 * @method static void setActive((Snapshot&Model)|null $snapshot)
 * @method static void clearActive()
 * @method static mixed withSnapshotActive(string|VersionNumber|Snapshot|null $snapshot, callable $callback)
 * @method static (Snapshot&Model)|null active()
 * @method static (Snapshot&Model)|null latest()
 * @method static (Snapshot&Model)|null working((Snapshot&Model)|null $snapshot)
 * @method static (Snapshot&Model)|null find($key)
 * @method static (Snapshot&Model)|null byNumber(string $number)
 * @method static Collection<Snapshot&Model> all()
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
