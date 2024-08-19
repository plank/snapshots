<?php

namespace Plank\Snapshots\Relationships;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\AsIdentifyingRelationship;

class IdentifyingMorphToMany extends MorphToMany
{
    use AsIdentifyingRelationship;
}
