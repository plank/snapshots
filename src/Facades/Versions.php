<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;

/**
 * @method void setActive(?Version $version)
 * @method void clearActive()
 * @method Version|null active()
 * @method Version|null latest()
 * @method Version|null find($key)
 * @method Version|null byNumber(string $number)
 * @method Collection all()
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
