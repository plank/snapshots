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

    /**
     * Add a Version's prefix to a migration name
     */
    public function addMigrationPrefix(Version $version, string $migration): string;

    /**
     * Strip a Version's prefix from a migration name
     */
    public function stripMigrationPrefix(string $migration): string;

    /**
     * Resolve a Version from a migration name.
     */
    public function versionFromMigration(string $name): ?Version;
}
