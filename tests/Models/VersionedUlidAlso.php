<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Versioned;

class VersionedUlidAlso extends Model implements Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    public function unversionedUlidAlsos(): HasMany
    {
        return $this->hasMany(UnversionedUlidAlso::class);
    }
}
