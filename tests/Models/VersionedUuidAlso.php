<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;

class VersionedUuidAlso extends Model implements Snapshotted
{
    use AsSnapshottedContent;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function unversionedUuidAlsos(): HasMany
    {
        return $this->hasMany(UnversionedUuidAlso::class);
    }
}
