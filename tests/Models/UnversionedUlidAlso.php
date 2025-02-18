<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;

class UnversionedUlidAlso extends Model
{
    use HasFactory;
    use HasUlids;
    use InteractsWithVersionedContent;

    protected $guarded = [];
}
