<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted as SnapshottedContract;

class VersionedAlso extends Model implements SnapshottedContract
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    public function unversionedAlsos(): HasMany
    {
        return $this->hasMany(UnversionedAlso::class);
    }
}
