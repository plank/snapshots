<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Plank\Snapshots\Models\Existence;

/**
 * @property-read Collection<Existence> $existences
 * @property-read ?Existence $existence
 */
interface Trackable extends Identifiable
{
    public function existences(): MorphMany;

    public function existence(): MorphOne;
}
