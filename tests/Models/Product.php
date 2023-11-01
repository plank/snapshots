<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Tests\Database\Factories\ProductFactory;

class Product extends Model implements Trackable, Versioned
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
        return ProductFactory::new();
    }

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
