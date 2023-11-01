<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Tests\Database\Factories\FlagFactory;

class Flag extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasHistory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return FlagFactory::new();
    }
}
