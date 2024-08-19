<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Model;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Facades\Versions;

/**
 * @mixin Model
 */
trait AsVersionedContent
{
    use HasHistory;
    use HushesHandlers;
    use InteractsWithVersionedContent;

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

        if ($version = Versions::active()) {
            $table = $version->addTablePrefix($table);
        }

        return $table;
    }
}
