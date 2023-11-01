<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Versioned
{
    public function visibility(): Attribute;
}
