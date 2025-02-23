<?php

namespace Plank\Snapshots\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Plank\Snapshots\Contracts\ResolvesModels;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\Exceptions\ResolveModelException;
use ReflectionClass;
use Throwable;

class ModelRepository implements ResolvesModels
{
    /**
     * @var array<string,class-string<Model>> A map of tables to their model class
     */
    protected array $map;

    public function __construct()
    {
        if (! File::exists($autoload = base_path('vendor/composer/autoload_classmap.php'))) {
            throw ResolveModelException::missingAutoloader();
        }

        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

        $this->map = Collection::make(require $autoload)
            ->keys()
            ->reject(fn (string $class) => static::shouldSkipClass($class))
            ->mapWithKeys(function ($class) use ($keyClass) {
                try {
                    // First check if class exists without autoloading
                    if (! class_exists($class)) {
                        return [$class => null];
                    }

                    // Now check if it's a valid model
                    if (! static::isValidModel($class)) {
                        return [$class => null];
                    }

                    return static::getTableMapping($class, $keyClass);
                } catch (Throwable) {
                    return [$class => null];
                }
            })
            ->filter()
            ->all();

        if (empty($this->map)) {
            throw ResolveModelException::emptyMap();
        }
    }

    /**
     * Get an instance of the Model being used for versions.
     *
     * @return class-string<Model>|null
     */
    public function resolve(string $table): ?string
    {
        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

        return $this->map[$keyClass::strip($table)] ?? null;
    }

    /**
     * Check if a class should be skipped
     */
    protected static function shouldSkipClass(string $class): bool
    {
        $skipTestClasses = config()->get('snapshots.model_resolver.skip_tests')
            && (str_ends_with($class, 'Test') || str_contains($class, '\\Tests\\'));

        return $skipTestClasses
            || str($class)->startsWith(config()->get('snapshots.model_resolver.ignore'));
    }

    /**
     * Check if class is a valid non-abstract model
     */
    protected static function isValidModel(string $class): bool
    {
        try {
            if (! is_a($class, Model::class, true)) {
                return false;
            }

            $reflection = new ReflectionClass($class);

            // Skip traits, interfaces and abstract classes
            if ($reflection->isTrait() || $reflection->isInterface() || $reflection->isAbstract()) {
                return false;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get table mapping for a valid model class
     *
     * @param  class-string  $class
     * @param  class-string<VersionKey>  $keyClass
     * @return array<string, class-string|null>
     */
    protected static function getTableMapping(string $class, string $keyClass): array
    {
        try {
            $model = new $class;

            return [$keyClass::strip($model->getTable()) => $class];
        } catch (Throwable) {
            return [$class => null];
        }
    }
}
