<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ResolvesModels
{
    /**
     * Get an instance of the Model being used for versions.
     *
     * @return class-string<Model>|null
     */
    public function resolve(string $table): ?string;
}
