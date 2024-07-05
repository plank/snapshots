<?php

namespace Plank\Snapshots\Tests\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Concerns\IdentifiesContent;
use Plank\Snapshots\Contracts\Identifying;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Collection<Tag> $tags
 * @property-read Collection<Post> $related
 * @property-read Collection<Like> $likes
 * @property-read Collection<Seo> $seos
 */
class Post extends Model implements Trackable, Identifying, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasUuids;
    use IdentifiesContent;

    protected $primaryKey = 'uuid';

    protected $guarded = [];

    protected static array $identifyingRelationships = ['tags', 'related', 'videos'];

    protected static array $identifiesRelationships = ['associated'];

    protected static array $nonIdentifyingAttributes = ['updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): BelongsToMany
    {
        return $this->identifyingBelongsToMany(Post::class, 'post_post', 'post_id', 'related_id', 'uuid', 'uuid');
    }

    public function associated(): BelongsToMany
    {
        return $this->identifyingBelongsToMany(Post::class, null, 'related_id', 'post_id', 'uuid', 'uuid');
    }

    public function tags(): MorphToMany
    {
        return $this->identifyingMorphToMany(Tag::class, 'taggable');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'post_id', 'uuid');
    }

    public function seos(): HasMany
    {
        return $this->hasMany(Seo::class, 'post_id', 'uuid');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'post_id', 'uuid');
    }
}
