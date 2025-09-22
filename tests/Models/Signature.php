<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;

class Signature extends Model implements Snapshotted
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }
}
