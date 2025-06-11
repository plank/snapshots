<?php

namespace Plank\Snapshots\Migrator\Blueprint;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;

class SnapshotBlueprint extends Blueprint
{
    /**
     * Create a foreign ID column for the given model.
     */
    public function unversionedForeign($columns, $name = null): ForeignKeyDefinition
    {
        $command = new ForeignKeyDefinition(
            $this->indexCommand('unversionedForeign', $columns, $name)->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }

    /**
     * Create a foreign ID column for the given model.
     */
    public function unversionedForeignIdFor($model, $column = null): ForeignKeyDefinition
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column = $column ?: $model->getForeignKey();

        if ($model->getKeyType() === 'int') {
            return $this->unversionedForeignId($column)
                ->references($model->getKeyName())
                ->on($model->getTable());
        }

        $modelTraits = class_uses_recursive($model);

        if (in_array(HasUlids::class, $modelTraits, true)) {
            return $this->unversionedForeignUlid($column, 26)
                ->references($model->getKeyName())
                ->on($model->getTable());
        }

        return $this->unversionedForeignUuid($column)
            ->references($model->getKeyName())
            ->on($model->getTable());
    }

    /**
     * Create a new unsigned big integer (8-byte) column on the table.
     */
    public function unversionedForeignId($column): UnversionedForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new UnversionedForeignIdColumnDefinition($this, [
            'type' => 'bigInteger',
            'name' => $column,
            'autoIncrement' => false,
            'unsigned' => true,
        ]));
    }

    /**
     * Create a new UUID column on the table with a foreign key constraint.
     */
    public function unversionedForeignUuid($column): UnversionedForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new UnversionedForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
    }

    /**
     * Create a new ULID column on the table with a foreign key constraint.
     */
    public function unversionedForeignUlid($column, $length = 26): UnversionedForeignIdColumnDefinition
    {
        return $this->addColumnDefinition(new UnversionedForeignIdColumnDefinition($this, [
            'type' => 'char',
            'name' => $column,
            'length' => $length,
        ]));
    }

    /**
     * Indicate that the given unversioned foreign key should be dropped.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @param  string|null  $column
     * @return \Illuminate\Support\Fluent
     */
    public function dropConstrainedUnversionedForeignIdFor($model, $column = null)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        return $this->dropConstrainedUnversionedForeignId($column ?: $model->getForeignKey());
    }

    /**
     * Indicate that the given column and unversioned foreign key should be dropped.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Fluent
     */
    public function dropConstrainedUnversionedForeignId($column)
    {
        $this->dropUnversionedForeign([$column]);

        return $this->dropColumn($column);
    }

    /**
     * Indicate that the given unversioned foreign key should be dropped.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @param  string|null  $column
     * @return \Illuminate\Support\Fluent
     */
    public function dropUnversionedForeignIdFor($model, $column = null)
    {
        if (is_string($model)) {
            $model = new $model;
        }

        return $this->dropUnversionedForeign([$column ?: $model->getForeignKey()]);
    }

    /**
     * Indicate that the given unversioned foreign key should be dropped.
     *
     * @param  string|array  $index
     * @return \Illuminate\Support\Fluent
     */
    public function dropUnversionedForeign($index)
    {
        return $this->dropIndexCommand('dropUnversionedForeign', 'foreign', $index);
    }

    /**
     * {@inheritDoc}
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        $prefix = $this->connection->getTablePrefix();

        if ($index && $prefix && ! str_starts_with($index, $prefix)) {
            $index = $prefix.$index;
        }

        return parent::indexCommand($type, $columns, $index, $algorithm);
    }
}
