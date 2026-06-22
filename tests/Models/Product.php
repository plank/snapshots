<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Snapshotted;

class Product extends Model implements Trackable, Snapshotted
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->using(PurchasedProduct::class)
            ->withPivot('quantity');
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'productable')
            ->using(PurchasedProduct::class)
            ->withPivot('quantity');
    }
}
