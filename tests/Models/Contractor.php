<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class Contractor extends Model
{
    use HasFactory;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];

    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'contractable')
            ->using(Contract::class);
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'contractable')
            ->using(Contract::class);
    }
}
