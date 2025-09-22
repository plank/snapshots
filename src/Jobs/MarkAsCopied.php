<?php

namespace Plank\Snapshots\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\Snapshot;
use Plank\Snapshots\Events\DataCopied;

class MarkAsCopied implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Snapshot&Model $snapshot,
        public (Authenticatable&Model)|null $user,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $snapshot = $this->snapshot;
        $snapshot->copied = true;
        $snapshot->save();

        Event::dispatch(new DataCopied($snapshot, $this->user));
    }
}
