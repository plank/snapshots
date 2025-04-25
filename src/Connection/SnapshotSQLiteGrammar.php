<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Plank\Snapshots\Contracts\VersionKey;

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
            $this->addForeignKeys($this->getCommandsByName($blueprint, 'foreign')),
            $this->addUnversionedForeignKeys($this->getCommandsByName($blueprint, 'unversionedForeign')),
            $this->addPrimaryKeys($this->getCommandByName($blueprint, 'primary'))
        );
    }

    /**
     * Get the foreign key syntax for a table creation statement.
     *
     * @param  \Illuminate\Database\Schema\ForeignKeyDefinition[]  $foreignKeys
     * @return string|null
     */
    protected function addUnversionedForeignKeys($foreignKeys)
    {
        return (new Collection($foreignKeys))->reduce(function ($sql, ForeignKeyDefinition $foreign) {
            /** @var class-string<VersionKey> $keyClass */
            $keyClass = config('snapshots.value_objects.version_key');

            $foreign->on($keyClass::strip($foreign->on));

            // Once we have all the foreign key commands for the table creation statement
            // we'll loop through each of them and add them to the create table SQL we
            // are building, since SQLite needs foreign keys on the tables creation.
            return $sql.$this->getForeignKey($foreign);
        }, '');
    }
}
