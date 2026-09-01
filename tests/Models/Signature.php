<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;
use Plank\Snapshots\Contracts\Trackable;

class Signature extends Model implements Snapshotted, Trackable
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }
}
