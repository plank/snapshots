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
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\Existence;

class CopyTable implements ShouldQueue
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Grab the data from the "working version"
        $working = Versions::working($this->version);

        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

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

        /** @var class-string<Existence> $existence */
        $existence = config()->get('snapshots.models.existence');

        Versions::withVersionActive($working, function () use ($class, $existence) {
            $class::query()
                ->with('existence')
                ->cursor()
                ->each(fn (Versioned&Model $model) => $existence::copiedTo($model, $this->version));
        });
    }
}
