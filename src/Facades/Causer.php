<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Facades\Facade;
use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Contracts\ResolvesCauser;

/**
 * @method static CausesChanges|null active()
 */
class Causer extends Facade
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
        return ResolvesCauser::class;
    }
}
