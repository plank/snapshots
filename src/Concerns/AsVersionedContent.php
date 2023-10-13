<?php

namespace Plank\Snapshots\Concerns;

use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Models\Version;

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
        $version = app(VersionContract::class);

        // Ensure we are starting from the user/framework defined table name
        $table = $version::stripMigrationPrefix(parent::getTable());

        if ($version = $this->versions->active()) {
            $table = $version->addTablePrefix($table);
        }

        return $table;
    }
}
