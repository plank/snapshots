<?php

namespace Plank\Snapshots\Concerns;

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
     * getTable() can be called so frequently that you can see up to a
     * ~10% performance increase from memoization in some cases
     */
    protected array $resolvedTables = [];

    /**
     * Retrieve the active version of the model.
     */
    public function activeVersion(): ?static
    {
        return static::query()->find($this->getKey());
    }

    public function getTable()
    {
        $table = parent::getTable();
        $version = Versions::active();
        $cacheKey = str_contains($table, 'laravel_reserved_')
            ? (preg_match('/laravel_reserved_[0-9]+/', $table, $m) ? $m[0] : $table)
            : ($version?->key()->toString() ?? '__none__');

        if (isset($this->resolvedTables[$cacheKey])) {
            return $this->resolvedTables[$cacheKey];
        }

        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

        $table = $keyClass::strip($table);

        if (str_contains($table, 'laravel_reserved_')) {
            $this->resolvedTables[$cacheKey] = $table;
        } elseif ($version) {
            $this->resolvedTables[$cacheKey] = $version->key()->prefix($table);
        } else {
            $this->resolvedTables[$cacheKey] = $table;
        }

        return $this->resolvedTables[$cacheKey];
    }
}
