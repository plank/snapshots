<?php

namespace Plank\Snapshots\Migrator\Blueprint\Macros;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Plank\Snapshots\Contracts\VersionKey;

/**
 * Create a method to register foreign keys without the version prefix
 *
 * @mixin \Illuminate\Database\Schema\Grammars\Grammar
 */
class UnversionedForeignKey
{
    public function __invoke()
    {
        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config('snapshots.value_objects.version_key');

        return function (Blueprint $blueprint, Fluent $command, Connection $connection) use ($keyClass) {
            $previousPrefix = $this->getTablePrefix();

            try {
                $this->setTablePrefix($keyClass::strip($previousPrefix));
                $sql = $this->compileForeign($blueprint, $command, $connection);

                return $sql;
            } finally {
                $this->setTablePrefix($previousPrefix);
            }
        };
    }
}
