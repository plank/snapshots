<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Facades\Versions;

/**
 * @mixin Model
 */
trait AsVersionedContent
{
    use HasTrackedExistence;
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
        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

        $table = $keyClass::strip(parent::getTable());

        if (str_contains($table, 'laravel_reserved_')) {
            return $table;
        }

        if ($version = Versions::active()) {
            $table = $version->key()->prefix($table);
        }

        return $table;
    }

    public static function snapshotQuery(): Builder
    {
        return static::query()->withoutGlobalScopes();
    }
}
