<?php

namespace Plank\Snapshots\Concerns;

use Plank\Snapshots\Contracts\ManagesVersions;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AsVersionedContent
{
    use InteractsWithVersionedContent;

    protected ManagesVersions $versions;

    /**
     * Retrieve the active version of the model.
     */
    public function activeVersion(): ?static
    {
        return static::query()->find($this->getKey());
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();

        if ($version = $this->versions->active()) {
            return $version->addTablePrefix($table);
        }

        return $table;
    }
}
