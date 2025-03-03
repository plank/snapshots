<?php

namespace Plank\Snapshots\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelModelResolver\Facades\Models;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Facades\Versions;

class CopyTable extends Copier
{
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

        if (config('snapshots.observers.history') && $model = Models::fromTable($to)) {
            $this->writeHistory($model);
        }
    }
}
