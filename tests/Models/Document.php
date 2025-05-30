<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;

class Document extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasHistory;

    protected $guarded = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array<string>
     */
    protected $visible = [
        'title',
        'text',
        'released_at',
    ];

    public function signatures(): BelongsToMany
    {
        return $this->belongsToMany(Signature::class);
    }
}
