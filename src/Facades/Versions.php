<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;

/**
 * @method static void setActive(?Version $version)
 * @method static void clearActive()
 * @method static mixed withVersionActive(string|VersionNumber|Version|null $version, callable $callback)
 * @method static Version|null active()
 * @method static Version|null latest()
 * @method static Version|null working(Version|null $version)
 * @method static Version|null find($key)
 * @method static Version|null byNumber(string $number)
 * @method static Collection all()
 */
class Versions extends Facade
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
        return ManagesVersions::class;
    }
}
