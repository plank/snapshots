<?php

namespace Plank\Snapshots\Facades;

use Illuminate\Support\Facades\Facade;

class SchemaCopier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TableCopier::class;
    }
}
