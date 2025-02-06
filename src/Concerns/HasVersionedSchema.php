<?php

namespace Plank\Snapshots\Concerns;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Events\TableCreated;
use Plank\Snapshots\Exceptions\MigrationFormatException;
use Plank\Snapshots\Migrator\SnapshotBlueprint;

trait HasVersionedSchema
{
    public function __construct(
        Connection $connection,
        protected ManagesVersions $versions,
        protected ManagesCreatedTables $tables,
    ) {
        parent::__construct($connection);

        $this->blueprintResolver(fn ($table, $callback, $prefix) => new SnapshotBlueprint($table, $callback, $prefix));
    }

    /**
     * {@inheritDoc}
     */
    public function addMigrationPrefix(Version $version, string $migration): string
    {
        return $version->number->key().'_'.$migration;
    }

    /**
     * {@inheritDoc}
     */
    public function stripMigrationPrefix(string $migration): string
    {
        $regex = config('snapshots.migration_regex');

        $matches = [];

        if (preg_match($regex, $migration, $matches) !== 1) {
            throw MigrationFormatException::create($migration);
        }

        return $matches[0];
    }

    /**
     * {@inheritDoc}
     */
    public function versionFromMigration(string $migration): ?Version
    {
        /** @var class-string<VersionKey> $class */
        $keyClass = config('snapshots.value_objects.version_number');

        $stripped = $this->stripMigrationPrefix($migration);

        $prefix = str($migration)->before('_'.$stripped);

        try {
            $key = $keyClass::fromVersionString($prefix);
        } catch (InvalidArgumentException) {
            return null;
        }

        return $this->versions->byNumber($key);
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

        $this->tables->queue(new TableCreated($original, $active));
    }

    /**
     * {@inheritDoc}
     */
    public function createForModel(string $class, Closure $callback): void
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('Models used in migrations must be instances of '.Model::class.'.');
        }

        $active = $this->versions->active();
        $table = (new $class)->getTable();

        $original = $active
            ? $active->stripTablePrefix($table)
            : $table;

        parent::create($table, $callback);

        $this->tables->queue(new TableCreated($original, $active, $class));
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
    public function dropForModel($class): void
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
    public function model(string $class, Closure $callback): void
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('Models used in migrations must be instances of '.Model::class.'.');
        }

        $this->table(((new $class)->getTable()), $callback);
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
    public function getColumnType($table, $column, $fullDefinition = false)
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
        }

        return parent::getColumnType($table, $column, $fullDefinition);
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

    /**
     * Get the columns for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumns($table)
    {
        if ($active = $this->versions->active()) {
            $table = $active->addTablePrefix($table);
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
            $table = $active->addTablePrefix($table);
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
            $table = $active->addTablePrefix($table);
        }

        return parent::getForeignKeys($table);
    }
}
