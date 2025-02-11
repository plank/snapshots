<?php

namespace Plank\Snapshots\Concerns;

use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;

trait HasVersionedConnection
{
    protected ManagesVersions $versions;

    protected ManagesCreatedTables $tables;

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO|\Closure  $pdo
     * @param  string  $database
     * @param  string  $tablePrefix
     * @return void
     */
    public function __construct(
        $pdo,
        $database,
        $tablePrefix,
        array $config,
        ManagesVersions $versions,
        ManagesCreatedTables $tables,
    ) {
        $this->versions = $versions;
        $this->tables = $tables;

        $config['name'] = $config['name'].'_snapshots';

        parent::__construct($pdo, $database, $tablePrefix, $config);
    }
}
