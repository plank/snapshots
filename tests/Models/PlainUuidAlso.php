<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class PlainUuidAlso extends Model
{
    use HasFactory;
    use HasUuids;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];
}
