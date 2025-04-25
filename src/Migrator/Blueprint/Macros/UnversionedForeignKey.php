<?php

namespace Plank\Snapshots\Migrator\Blueprint\Macros;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
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

        return function (Blueprint $blueprint, ForeignKeyDefinition $foreign, Connection $connection) use ($keyClass) {
            $foreign->on($keyClass::strip($foreign->on));

            return $this->compileForeign($blueprint, $foreign, $connection);
        };
    }
}
