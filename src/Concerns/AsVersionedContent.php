<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\ValueObjects\Revision;

/**
 * @mixin Model
 *
 * @property-read Collection<Revision> $visibility
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

    public function visibility(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Revision::collection($this->visibleHistory());
            })->shouldCache();
    }
}
