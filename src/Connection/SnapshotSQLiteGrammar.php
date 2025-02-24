<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class SnapshotSQLiteGrammar extends SQLiteGrammar
{
    /**
     * Compile a create table command.
     *
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('%s table %s (%s%s%s%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint)),
            $this->addForeignKeys($blueprint),
            $this->addUnversionedForeignKeys($blueprint),
            $this->addPrimaryKeys($blueprint)
        );
    }

    /**
     * Get the foreign key syntax for a table creation statement.
     *
     * @param  \Illuminate\Database\Schema\ForeignKeyDefinition[]  $foreignKeys
     * @return string|null
     */
    protected function addUnversionedForeignKeys($blueprint)
    {
        $foreigns = $this->getCommandsByName($blueprint, 'unversionedForeign');

        return collect($foreigns)->reduce(function ($sql, $foreign) {
            // Once we have all the foreign key commands for the table creation statement
            // we'll loop through each of them and add them to the create table SQL we
            // are building, since SQLite needs foreign keys on the tables creation.
            $sql .= $this->withoutPrefix(fn () => $this->getForeignKey($foreign));

            if (! is_null($foreign->onDelete)) {
                $sql .= " on delete {$foreign->onDelete}";
            }

            // If this foreign key specifies the action to be taken on update we will add
            // that to the statement here. We'll append it to this SQL and then return
            // the SQL so we can keep adding any other foreign constraints onto this.
            if (! is_null($foreign->onUpdate)) {
                $sql .= " on update {$foreign->onUpdate}";
            }

            return $sql;
        }, '');
    }

    protected function withoutPrefix(\Closure $callback)
    {
        try {
            $keyClass = config('snapshots.value_objects.version_key');
            $previousPrefix = $this->getTablePrefix();
            $this->setTablePrefix($keyClass::strip($previousPrefix));

            return $callback();
        } finally {
            $this->setTablePrefix($previousPrefix);
        }
    }
}
