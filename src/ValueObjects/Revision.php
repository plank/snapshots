<?php

namespace Plank\Snapshots\ValueObjects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Models\History;

class Revision
{
    public function __construct(
        public Version $version,
        public Model $content,
        public bool $hidden,
    ) {
    }

    public static function fromHistory(History $history): self
    {
        return new self(
            version: $history->version,
            content: $history->trackable,
            hidden: $history->trackable->hidden
        );
    }

    /**
     * @param  Collection<History>  $history
     */
    public static function collection(Collection $history): Collection
    {
        return $history
            ->sortByDesc(function (History $item) {
                return $item->version->number;
            }, SORT_NATURAL)
            ->groupBy('version_id')
            ->map(function (Collection $items) {
                return $items->sortByDesc('created_at')->first();
            })
            ->map(fn (History $item) => static::fromHistory($item));
    }
}
