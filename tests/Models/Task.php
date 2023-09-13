<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\InteractsWithVersionedContent;
use Plank\Snapshots\Tests\Database\Factories\TaskFactory;

class Task extends Model
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
        return TaskFactory::new();
    }

    public function contractors(): MorphToMany
    {
        return $this->morphToMany(Contractor::class, 'contractable')
            ->using(Contract::class);
    }
}
