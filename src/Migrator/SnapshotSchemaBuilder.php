<?php

namespace Plank\Snapshots\Migrator;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Plank\Snapshots\Contracts\CopiesTables;
use Plank\Snapshots\Contracts\ManagesVersions;

/**
 * @mixin Builder
 */
class SnapshotSchemaBuilder extends Builder
{
    public function __construct(
        Connection $connection,
        protected CopiesTables $tableCopier,
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

        if (parent::hasTable($original)) {
            $this->withoutForeignKeyConstraints(function () use ($table, $original) {
                $this->tableCopier->copy($original, $table);
            });
        }
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
