<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class Unversioned extends Model
{
    use HasFactory;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];
}
