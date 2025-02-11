<?php

namespace Plank\Snapshots\Contracts;

use Closure;

interface VersionedSchema
{
    /**
     * Alter a table on the schema using its Model.
     *
     * @param  class-string<Model>  $model
     */
    public function model(string $class, Closure $callback);

    /**
     * Create a new table on the schema using its Model.
     *
     * @param  class-string<Model>  $model
     */
    public function createForModel(string $model, Closure $callback);

    /**
     * Drop a table from the schema using its Model.
     *
     * @param  class-string<Model>  $model
     * @param  \Closure  $callback
     */
    public function dropForModel($model);
}
