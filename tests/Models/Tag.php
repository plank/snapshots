<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\IdentifiesContent;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;
use Plank\Snapshots\Contracts\Identifying;

class Tag extends Model implements Identifying
{
    use HasFactory;
    use IdentifiesContent;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];

    protected static array $identifiesRelationships = ['posts'];

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    /**
     * Get all of the videos that are assigned this tag.
     */
    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}
