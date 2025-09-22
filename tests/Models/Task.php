<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Plank\Snapshots\Concerns\InteractsWithSnapshottedContent;

class Task extends Model
{
    use HasFactory;
    use InteractsWithSnapshottedContent;

    protected $guarded = [];

    public function contractors(): MorphToMany
    {
        return $this->morphToMany(Contractor::class, 'contractable')
            ->using(Contract::class);
    }
}
