<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Support\Collection;

/**
 * @property string $hash
 */
interface Identifying
{
    /**
     * Update the hashes of all related models that rely on this model for identification
     */
    public function updateRelatedHashes(): void;

    /**
     * @return Collection<string>
     */
    public static function identifiesRelationships(): Collection;
}
