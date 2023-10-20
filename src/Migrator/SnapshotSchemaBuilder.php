<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\Versioned;
use Plank\Snapshots\Events\TableCreated;

/**
 * @mixin Builder
 */
class SnapshotSchemaBuilder extends Builder
{
    public function __construct(
        Connection $connection,
        protected ManagesVersions $versions
    ) {
        parent::__construct($connection);

        $this->blueprintResolver(fn ($table, $callback, $prefix) => new SnapshotBlueprint($table, $callback, $prefix));
    }

    /**
     * {@inheritDoc}
     */
    public function create($table, Closure $callback): void
    {
        $original = $table;

        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        parent::create($table, $callback);

        $this->withoutForeignKeyConstraints(function () use ($active, $original) {
            Event::dispatch(new TableCreated($original, $active));
        });
    }

    /**
     * Create a new table on the schema.
     *
     * @param  class-string<Model>  $model
     */
    public function createForModel(string $model, Closure $callback): void
    {
        if (! is_a($model, Model::class, true) || ! is_a($model, Versioned::class, true)) {
            throw new InvalidArgumentException('Models for snapshotted tables must implement '.Versioned::class.' and extend '.Model::class.'.');
        }

        $active = $this->versions->active();
        $table = (new $model)->getTable();
        $original = app(Version::class)::stripMigrationPrefix($table);

        parent::create($table, $callback);

        $this->withoutForeignKeyConstraints(function () use ($original, $active, $model) {
            Event::dispatch(new TableCreated($original, $active, $model));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function drop($table): void
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        parent::drop($table);
    }

    /**
     * Create a new table on the schema.
     *
     * @param  class-string<Model>  $model
     * @param  \Closure  $callback
     */
    public function dropForModel($model): void
    {
        if (! is_a($model, Model::class, true) || ! is_a($model, Versioned::class, true)) {
            throw new InvalidArgumentException('Models for snapshotted tables must implement '.Versioned::class.' and extend '.Model::class.'.');
        }

        $table = (new $model)->getTable();

        parent::drop($table);
    }

    /**
     * {@inheritDoc}
     */
    public function dropIfExists($table): void
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        parent::dropIfExists($table);
    }

    /**
     * {@inheritDoc}
     */
    public function table($table, Closure $callback): void
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        parent::table($table, $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function rename($from, $to): void
    {
        if ($active = $this->versions->active()) {
            $from = $active->addTablePrefix($from);
            $to = $active->addTablePrefix($to);
        }

        parent::rename($from, $to);
    }

    /**
     * {@inheritDoc}
     */
    public function hasTable($table): bool
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::hasTable($table);
    }

    /**
     * {@inheritDoc}
     */
    public function hasColumn($table, $column): bool
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::hasColumn($table, $column);
    }

    /**
     * {@inheritDoc}
     */
    public function hasColumns($table, array $columns): bool
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::hasColumns($table, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function whenTableHasColumn(string $table, string $column, Closure $callback)
    {
        if ($this->hasColumn($table, $column)) {
            $this->table($table, fn (Blueprint $table) => $callback($table));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function whenTableDoesntHaveColumn(string $table, string $column, Closure $callback)
    {
        if (! $this->hasColumn($table, $column)) {
            $this->table($table, fn (Blueprint $table) => $callback($table));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dropColumns($table, $columns): void
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        parent::dropColumns($table, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnType($table, $column)
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::getColumnType($table, $column);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnListing($table): array
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::getColumnListing($table);
    }
}
