<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;

class UnversionedUuidAlso extends Model
{
    use HasFactory;
    use HasUuids;
    use InteractsWithVersionedContent;

    protected $guarded = [];
}
