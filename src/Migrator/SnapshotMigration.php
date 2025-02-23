<?php

namespace Plank\Snapshots\Migrator;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

abstract class SnapshotMigration extends Migration
{
    public function getConnection()
    {
        return ($this->connection ?? DB::getDefaultConnection()).'_snapshots';
    }
}
