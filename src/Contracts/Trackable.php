<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\ValueObjects\Revision;

/**
 * @property-read Collection<History> $history
 * @property-read Collection<Revision> $visibility
 * @property-read bool $hidden
 */
interface Trackable
{
    /**
     * Get the history of changes to this model and what versions they occured in.
     */
    public function history(): MorphMany;

    /**
     * Determine if the model exists but is soft deleted
     */
    public function hidden(): Attribute;

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();

    /**
     * Get the value of the model's primary key.
     *
     * @return string|int
     */
    public function getKey();
}
