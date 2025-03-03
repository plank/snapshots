<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Plank\Snapshots\Models\History;

/**
 * @property-read Collection<History> $history
 * @property-read bool $hidden
 */
interface Trackable extends Identifiable
{
    /**
     * Get the history of changes to this model and what versions they occured in.
     */
    public function history(): MorphMany;

    /**
     * Get the active history item for the current version.
     */
    public function activeHistoryItem(): ?History;

    /**
     * Determine if the model exists but is soft deleted
     */
    public function hidden(): Attribute;

    /**
     * Get a set of attributes for the model which are safe to track
     */
    public function trackableAttributes(): array;

    /**
     * Get a the original set of attributes for the model which are safe to track
     */
    public function trackableOriginal(): array;

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
