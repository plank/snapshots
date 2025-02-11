<?php

namespace Plank\Snapshots\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\VersionedConnection;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Migrator\SnapshotBlueprint;

trait HasVersionedSchema
{
    public function __construct(
        VersionedConnection $connection,
        protected ManagesVersions $versions,
        protected ManagesCreatedTables $tables,
    ) {
        parent::__construct($connection);

        $this->blueprintResolver(fn ($table, $callback, $prefix) => new SnapshotBlueprint($table, $callback, $prefix));
    }

    /**
     * {@inheritDoc}
     */
    public function create($table, Closure $callback)
    {
        $original = $table;

        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        parent::create($table, $callback);

        $this->tables->queue(new TableCreated($original, $active));
    }

    /**
     * {@inheritDoc}
     */
    public function createForModel(string $class, Closure $callback)
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('Models used in migrations must be instances of '.Model::class.'.');
        }

        $active = $this->versions->active();
        $table = (new $class)->getTable();

        $original = $active
            ? $active->key()->strip($table)
            : $table;

        parent::create($table, $callback);

        $this->tables->queue(new TableCreated($original, $active, $class));
    }

    /**
     * {@inheritDoc}
     */
    public function drop($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        parent::drop($table);
    }

    /**
     * {@inheritDoc}
     */
    public function dropForModel($class)
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('Models used in migrations must be instances of '.Model::class.'.');
        }

        $table = (new $class)->getTable();

        parent::drop($table);
    }

    /**
     * {@inheritDoc}
     */
    public function dropIfExists($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        parent::dropIfExists($table);
    }

    /**
     * {@inheritDoc}
     */
    public function table($table, Closure $callback)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        parent::table($table, $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function model(string $class, Closure $callback)
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('Models used in migrations must be instances of '.Model::class.'.');
        }

        $this->table(((new $class)->getTable()), $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function rename($from, $to)
    {
        if ($active = $this->versions->active()) {
            $from = $active->key()->prefix($from);
            $to = $active->key()->prefix($to);
        }

        parent::rename($from, $to);
    }

    /**
     * {@inheritDoc}
     */
    public function hasTable($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::hasTable($table);
    }

    /**
     * {@inheritDoc}
     */
    public function hasColumn($table, $column)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::hasColumn($table, $column);
    }

    /**
     * {@inheritDoc}
     */
    public function hasColumns($table, array $columns)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::hasColumns($table, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function dropColumns($table, $columns)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        parent::dropColumns($table, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnType($table, $column, $fullDefinition = false)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::getColumnType($table, $column, $fullDefinition);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnListing($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::getColumnListing($table);
    }

    /**
     * Get the columns for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumns($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::getColumns($table);
    }

    /**
     * Get the indexes for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getIndexes($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::getIndexes($table);
    }

    /**
     * Get the foreign keys for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getForeignKeys($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->key()->prefix($table);
        }

        return parent::getForeignKeys($table);
    }
}
