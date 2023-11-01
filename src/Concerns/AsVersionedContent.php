<?php

namespace Plank\Snapshots\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Plank\LaravelHush\Concerns\HushesHandlers;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\ValueObjects\Revision;
use Plank\Snapshots\ValueObjects\VersionNumber;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<Revision> $visibility
 * @property-read Collection<History> $visibileHistory
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

        if ($version = $this->versions->active()) {
            $table = $version->addTablePrefix($table);
        }

        return $table;
    }

    /**
     * @param  callable(\Illuminate\Database\Eloquent\Query $query)  $callback
     */
    public static function withVersionActive(string|VersionNumber|VersionContract $version, Closure $callback): mixed
    {
        /** @var ManagesVersions $versions */
        $versions = app(ManagesVersions::class);

        $active = $versions->active();

        $versions->setActive($version);

        $result = $callback(static::query());

        $versions->setActive($active);

        return $result;
    }

    public function visibility(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Revision::collection($this->visibleHistory());
            })->shouldCache();
    }

    public function visibleHistory(): Collection
    {
        return $this->history()
            ->with('version')
            ->get()
            ->groupBy('version_id')
            ->map(function (Collection $items) {
                $item = $items->sortByDesc('created_at')->first();

                $trackable = static::withVersionActive($item->version, function (Builder $query) use ($item) {
                    return $query->withoutGlobalScopes()
                        ->whereKey($item->trackable_id)
                        ->first();
                });

                $item->setRelation('trackable', $trackable);

                return $item;
            })
            ->reject(fn (History $item) => $item->trackable === null);
    }
}
