<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\ManagesVersions;

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
