<?php

namespace Plank\Snapshots\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\DataCopied;
use Plank\Snapshots\Events\VersionMigrated;

class MarkAsCopied implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(VersionMigrated $event)
    {
        $version = $event->version;
        $version->copied = true;
        $version->save();

        Event::dispatch(new DataCopied($version, $event->causer));
    }
}
