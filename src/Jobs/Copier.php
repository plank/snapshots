<?php

namespace Plank\Snapshots\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Facades\Causer;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\History;

abstract class Copier implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public string $table,
    ) {}

    abstract public function handle();

    /**
     * @param  class-string<Model&Trackable>  $model
     */
    protected function writeHistory(string $model)
    {
        /** @var class-string<History>|null */
        $history = config()->get('snapshots.models.history');

        /** @var CausesChanges|null $causer */
        $causer = Causer::active();

        $version = $this->version;
        $working = Versions::working($this->version);

        $history::query()
            ->where('trackable_type', $model)
            ->where('version_id', $working?->getKey())
            ->cursor()
            ->groupBy('trackable_id')
            ->each(function (Collection $items) use ($version, $history, $causer) {
                // Move all History items from the working version to the newly created version
                $items->each(function (History $item) use ($version, $history) {
                    $history::withoutTimestamps(function () use ($item, $version) {
                        $item->updateQuietly([
                            'version_id' => $version->getKey(),
                        ]);
                    });
                });

                $this->createSnapshottedHistoryItem($items->sortByDesc('created_at')->first(), $history, $causer);
            });
    }

    /**
     * In the working version, create a new History item that represents the state of the model
     * as the starting point for new changes since the newly created version.
     */
    protected function createSnapshottedHistoryItem(History $item, string $history, ?CausesChanges $causer): void
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

        if (config()->get('snapshots.observers.identity')) {
            $data['hash'] = $trackable->newHash();
        }

        $history::create($data);
    }
}
