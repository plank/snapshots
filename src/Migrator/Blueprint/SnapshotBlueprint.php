<?php

namespace Plank\Snapshots\Migrator\Blueprint;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;

class SnapshotBlueprint extends Blueprint
{
    /**
     * {@inheritDoc}
     */
    public function addAlterCommands()
    {
        if ($this->grammar instanceof SQLiteGrammar) {
            foreach ($this->commands as $command) {
                if ($command->name === 'dropPlainForeign') {
                    $command->name = 'dropForeign';
                }
            }
        }

        parent::addAlterCommands();
    }

    /**
     * Create a foreign ID column for the given model.
     */
    public function plainForeign($columns, $name = null): ForeignKeyDefinition
    {
        $command = new ForeignKeyDefinition(
            $this->indexCommand('plainForeign', $columns, $name)->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }

    /**
     * Create a foreign ID column for the given model.
     */
    public function plainForeignIdFor($model, $column = null): ForeignKeyDefinition
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column = $column ?: $model->getForeignKey();

        if ($model->getKeyType() === 'int') {
            return $this->plainForeignId($column)
                ->references($model->getKeyName())
                ->on($model->getTable());
        }

        $modelTraits = class_uses_recursive($model);

        if (in_array(HasUlids::class, $modelTraits, true)) {
            return $this->plainForeignUlid($column, 26)
                ->references($model->getKeyName())
                ->on($model->getTable());
        }

        return $this->plainForeignUuid($column)
            ->references($model->getKeyName())
            ->on($model->getTable());
    }

    /**
     * Create a new unsigned big integer (8-byte) column on the table.
     */
    public function plainForeignId($column): PlainForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new PlainForeignIdColumnDefinition($this, [
            'type' => 'bigInteger',
            'name' => $column,
            'autoIncrement' => false,
            'unsigned' => true,
        ]));
    }

    /**
     * Create a new UUID column on the table with a foreign key constraint.
     */
    public function plainForeignUuid($column): PlainForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new PlainForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
    }

    /**
     * Create a new ULID column on the table with a foreign key constraint.
     */
    public function plainForeignUlid($column, $length = 26): PlainForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new PlainForeignIdColumnDefinition($this, [
            'type' => 'char',
            'name' => $column,
            'length' => $length,
        ]));
    }

    /**
     * Indicate that the given plain foreign key should be dropped.
     *
     * @param  Model|string  $model
     * @param  string|null  $column
     * @return Fluent
     */
    public function dropConstrainedPlainForeignIdFor($model, $column = null)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        return $this->dropConstrainedPlainForeignId($column ?: $model->getForeignKey());
    }

    /**
     * Indicate that the given column and plain foreign key should be dropped.
     *
     * @param  string  $column
     * @return Fluent
     */
    public function dropConstrainedPlainForeignId($column)
    {
        $this->dropPlainForeign([$column]);

        return $this->dropColumn($column);
    }

    /**
     * Indicate that the given plain foreign key should be dropped.
     *
     * @param  Model|string  $model
     * @param  string|null  $column
     * @return Fluent
     */
    public function dropPlainForeignIdFor($model, $column = null)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        return $this->dropPlainForeign([$column ?: $model->getForeignKey()]);
    }

    /**
     * Indicate that the given plain foreign key should be dropped.
     *
     * @param  string|array  $index
     * @return Fluent
     */
    public function dropPlainForeign($index)
    {
        return $this->dropIndexCommand('dropPlainForeign', 'plainForeign', $index);
    }

    /**
     * {@inheritDoc}
     *
     * Use getTablePrefix() directly so dynamically-set snapshot prefixes are
     * reflected in auto-generated index names without needing prefix_indexes
     * to be set in the static connection config.
     */
    protected function createIndexName($type, array $columns)
    {
        $index = strtolower($this->prefixedTable().'_'.implode('_', $columns).'_'.$type);

        return str_replace(['-', '.'], '_', $index);
    }

    /**
     * Returns the table name with the connection's current prefix applied,
     * correctly handling schema-qualified names (e.g. PostgreSQL "public.users"
     * becomes "public.{prefix}users").
     */
    private function prefixedTable(): string
    {
        $prefix = $this->connection->getTablePrefix();

        if (! $prefix) {
            return $this->table;
        }

        $dot = strrpos($this->table, '.');

        return $dot !== false
            ? substr_replace($this->table, '.'.$prefix, $dot, 1)
            : $prefix.$this->table;
    }

    /**
     * {@inheritDoc}
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null, $operatorClass = null)
    {
        $prefix = $this->connection->getTablePrefix();

        if ($index && $prefix && ! str_starts_with($index, $prefix)) {
            $index = $prefix.$index;
        }

        return parent::indexCommand($type, $columns, $index, $algorithm, $operatorClass);
    }
}
