<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Versioned as VersionedContract;

class Versioned extends Model implements VersionedContract
{
    use AsVersionedContent;
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    public function unversionedUuids(): HasMany
    {
        return $this->hasMany(UnversionedUlid::class);
    }
}
