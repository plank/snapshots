<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;

class UnversionedAlso extends Model
{
    use HasFactory;
    use InteractsWithVersionedContent;

    protected $guarded = [];
}
