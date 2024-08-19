<?php

namespace Plank\Snapshots\Relationships;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\AsIdentifyingRelationship;

class IdentifyingBelongsToMany extends BelongsToMany
{
    use AsIdentifyingRelationship;
}
