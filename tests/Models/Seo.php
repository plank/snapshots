<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;
use Plank\Snapshots\Contracts\Trackable;

class Seo extends Model implements Snapshotted, Trackable
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'uuid');
    }
}
