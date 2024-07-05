<?php

namespace Plank\Snapshots\Contracts;

/**
 * @property string $hash
 */
interface Identifiable
{
    /**
     * Update the existing hash for the model
     */
    public function updateHash(): void;

    /**
     * Generate an identifying hash for this model including its identifying relationships
     */
    public function newHash(): string;

    /**
     * Generate an identifying hash for this model's attributes
     */
    public function modelhash(): string;
}
