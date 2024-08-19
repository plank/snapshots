<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;

class Category extends Model
{
    use HasFactory;
    use InteractsWithVersionedContent;

    protected $guarded = [];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'category_project', 'category_id', 'project_id', 'id', 'ulid')
            ->using(CategorizedProject::class);
    }
}
