<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;
use Plank\Snapshots\Tests\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;
    use InteractsWithVersionedContent;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'category_project', 'category_id', 'project_id', 'id', 'ulid')
            ->using(CategorizedProject::class);
    }
}
