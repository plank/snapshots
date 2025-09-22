<?php

namespace Plank\Snapshots\Repository;

use Illuminate\Database\Eloquent\Model;
use Plank\LaravelModelResolver\Repository\ModelRepository as BaseModelRepository;
use Plank\Snapshots\Contracts\VersionKey;
use Throwable;

class ModelRepository extends BaseModelRepository
{
    /** @var class-string<VersionKey> */
    protected static string $keyClass;

    public function __construct()
    {
        static::$keyClass = config()->get('snapshots.value_objects.version_key');

        parent::__construct();
    }

    /**
     * Resolve the class string of a model using its database table name
     *
     * @return class-string<Model>|null
     */
    public function fromTable(string $table): ?string
    {
        return $this->map[static::$keyClass::strip($table)] ?? null;
    }

    /**
     * Get table mapping for a valid model class
     *
     * @param  class-string  $class
     * @param  class-string<VersionKey>  $keyClass
     * @return array<string, class-string|null>
     */
    protected static function getTableMapping(string $class): ?array
    {
        try {
            $model = new $class;

            return [
                static::$keyClass::strip($model->getTable()),
                $class,
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
