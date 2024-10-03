<?php

namespace Plank\Snapshots\Contracts;

use Closure;

interface VersionedSchema
{
    /**
     * Create a new table on the schema.
     *
     * @param  class-string<Model>  $model
     */
    public function createForModel(string $model, Closure $callback): void;

    /**
     * Create a new table on the schema.
     *
     * @param  class-string<Model>  $model
     * @param  \Closure  $callback
     */
    public function dropForModel($model): void;
}
