<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Tests\Database\Factories\SeoFactory;

class Seo extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return SeoFactory::new();
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'uuid');
    }
}
