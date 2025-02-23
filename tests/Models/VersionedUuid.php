<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Versioned;

class VersionedUuid extends Model implements Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function unversionedUuids(): HasMany
    {
        return $this->hasMany(UnversionedUuid::class);
    }
}
