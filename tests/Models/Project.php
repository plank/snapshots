<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Tests\Database\Factories\ProjectFactory;

class Project extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasUlids;

    protected $guarded = [];

    protected $primaryKey = 'ulid';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return ProjectFactory::new();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_project', 'project_id', 'category_id', 'ulid', 'id')
            ->using(CategorizedProject::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_project', 'project_id', 'product_id', 'ulid', 'id')
            ->using(PurchasedProduct::class)
            ->withPivot('quantity');
    }

    public function contractors(): MorphToMany
    {
        return $this->morphToMany(Contractor::class, 'contractable')
            ->using(Contract::class);
    }

    public function plans(): MorphToMany
    {
        return $this->morphToMany(Plan::class, 'plannable')
            ->using(AssignedPlan::class)
            ->withPivot('accepted');
    }
}
