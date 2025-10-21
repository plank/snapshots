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
use Plank\Snapshots\Events\DataCopied;
use Plank\Snapshots\Models\Version;

/**
 * @property (Authenticatable&Model)|null $user
 */
class MarkAsCopied implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Version&Model $version,
        public ?Authenticatable $user,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $version = $this->version;
        $version->copied = true;
        $version->save();

        Event::dispatch(new DataCopied($version, $this->user));
    }
}
