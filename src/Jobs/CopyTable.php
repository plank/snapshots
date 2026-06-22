<?php

namespace Plank\Snapshots\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelModelResolver\Facades\Models;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Contracts\Snapshotted;
use Plank\Snapshots\Contracts\SnapshotKey;
use Plank\Snapshots\Facades\Snapshots;
use Plank\Snapshots\Models\Existence;

class CopyTable implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Snapshot&Model $snapshot,
        public string $table,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Grab the data from the "working snapshot"
        $working = Snapshots::working($this->snapshot);

        /** @var class-string<SnapshotKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.snapshot_key');

        $from = $working
            ? $working->key()->prefix($this->table)
            : $keyClass::strip($this->table);

        $to = $this->table;

        if ($from === $to) {
            return;
        }

        Schema::withoutForeignKeyConstraints(function () use ($from, $to) {
            DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
        });

        $class = Models::fromTable($to);

        if ($class === null || ! is_a($class, Trackable::class, true)) {
            return;
        }

        $this->createExistences($class, $working);
    }

    protected function createExistences(string $class, ?Snapshot $working): void
    {
        /** @var class-string<Existence> $existence */
        $existence = config()->get('snapshots.models.existence');

        if ($existence === null) {
            return;
        }

        Snapshots::withSnapshotActive($working, function () use ($class, $existence) {
            $class::query()
                ->with('existence')
                ->cursor()
                ->each(fn (Snapshotted&Model $model) => $existence::copiedTo($model, $this->snapshot));
        });
    }
}
