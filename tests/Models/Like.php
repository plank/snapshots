<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class Like extends Model
{
    use HasFactory;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'uuid');
    }
}
