<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Tests\Database\Factories\ImageFactory;

class Image extends Model implements Trackable
{
    use HasFactory;
    use HasHistory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return ImageFactory::new();
    }
}
