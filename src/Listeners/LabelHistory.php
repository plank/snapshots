<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Plank\LaravelModelResolver\Facades\Models;
use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Models\History;

class LabelHistory
{
    public function __construct(
        protected ManagesVersions $versions,
    ) {}

    public function handle(TableCopied $event)
    {
        $model = Models::fromTable($event->table);

        if ($model === null) {
            return;
        }

        $version = $this->versions->byKey($event->table);

        /** @var class-string<History>|null */
        $history = config()->get('snapshots.models.history');

        $history::query()
            ->where('trackable_type', $model)
            ->whereNull('version_id')
            ->cursor()
            ->groupBy('trackable_id')
            ->each(function (Collection $items) use ($version) {
                // Move all History items from the working version to the newly created version
                $items->each(function (History $item) use ($version) {
                    History::withoutTimestamps(function () use ($item, $version) {
                        $item->updateQuietly([
                            'version_id' => $version->getKey(),
                        ]);
                    });
                });

                $this->createSnapshottedHistoryItem($items->sortByDesc('created_at')->first());
            });
    }

    /**
     * In the working version, create a new History item that represents the state of the model
     * as the starting point for new changes since the newly created version.
     */
    protected function createSnapshottedHistoryItem(History $item): void
    {
        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($item->trackable_type));

        /** @var Trackable|null $trackable */
        $trackable = $item->trackable()
            ->when($softDeletes, fn ($query) => $query->withTrashed())
            ->whereKey($item->trackable_id)
            ->first();

        if ($trackable === null) {
            return;
        }

        /** @var CausesChanges|null $causer */
        $causer = app(ResolvesCauser::class)->active();

        /** @var class-string<History>|null $history */
        $history = config()->get('snapshots.models.history');

        $data = [
            'operation' => Operation::Snapshotted,
            'causer_id' => $causer?->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'version_id' => null,
            'trackable_id' => $trackable->getKey(),
            'trackable_type' => $trackable->getMorphClass(),
            'from' => null,
            'to' => $item->to,
        ];

        if (config()->get('snapshots.history.identity')) {
            $data['hash'] = $trackable->newHash();
        }

        $history::create($data);
    }
}
