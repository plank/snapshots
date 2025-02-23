<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Versioned as VersionedContract;

class VersionedAlso extends Model implements VersionedContract
{
    use AsVersionedContent;
    use HasFactory;

    protected $guarded = [];

    public function unversionedAlsos(): HasMany
    {
        return $this->hasMany(UnversionedAlso::class);
    }
}
