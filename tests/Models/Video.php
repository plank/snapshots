<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Concerns\IdentifiesContent;
use Plank\Snapshots\Contracts\Identifying;

class Video extends Model implements Identifying
{
    use HasFactory;
    use IdentifiesContent;
    use SoftDeletes;

    protected $guarded = [];

    protected static array $identifiesRelationships = ['post'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'uuid');
    }

    /**
     * Get all of the tags for the post.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
