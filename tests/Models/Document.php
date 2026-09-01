<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;
use Plank\Snapshots\Contracts\Trackable;

class Document extends Model implements Snapshotted, Trackable
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array<string>
     */
    protected $visible = [
        'title',
        'text',
        'released_at',
    ];

    public function signatures(): BelongsToMany
    {
        return $this->belongsToMany(Signature::class);
    }
}
