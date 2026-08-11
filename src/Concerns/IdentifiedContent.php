<?php

namespace Plank\Snapshots\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Plank\Snapshots\Contracts\Identifiable;

/**
 * @mixin Model
 * @mixin Identifiable
 *
 * @property array $identifyingRelationships
 * @property array $nonIdentifyingAttributes
 * @property string|null $hash
 */
trait IdentifiedContent
{
    use HasIdentifyingRelationships;

    public function hash(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->existence()->first()?->hash,
        )->withoutObjectCaching();
    }

    public function updateHash(): void
    {
        $existence = $this->existence()->first();

        if ($existence === null) {
            return;
        }

        $existence->update([
            'hash' => $this->newHash(),
        ]);
    }

    public function newHash(): string
    {
        // Read the persisted row through the write connection so the hash always
        // reflects what is stored, immune to in-memory cast re-encoding, and so a
        // read replica cannot hand us a stale/missing row mid-save.
        $fresh = $this->setKeysForSelectQuery($this->newQueryWithoutScopes())
            ->with(static::identifyingRelationships()->all())
            ->useWritePdo()
            ->first();

        if ($fresh === null) {
            return hash('sha256', 'null');
        }

        $identity = $fresh->modelHash();

        $identity .= static::identifyingRelationships()
            ->implode(fn (string $relationship) => $fresh->relatedHash($relationship), '');

        return hash('sha256', $identity);
    }

    public function modelHash(): string
    {
        $hidden = $this->getHidden();
        $visible = $this->getVisible();

        $identity = Collection::make($this->attributes)
            ->except(static::nonIdentifyingAttributes())
            ->except($hidden)
            ->when($this->ignoreTimestampsInIdentity(), function ($attributes) {
                return $attributes->except([
                    $this->getCreatedAtColumn(),
                    $this->getUpdatedAtColumn(),
                ]);
            })
            ->when($visible, fn ($attributes) => $attributes->only($visible))
            ->sortKeys()
            ->map(fn ($value, $key) => $key.':'.json_encode($value))
            ->implode(', ');

        return hash('sha256', $identity);
    }

    public function ignoreTimestampsInIdentity(): bool
    {
        return $this->usesTimestamps();
    }

    protected function relatedHash(string $relationship): string
    {
        $related = $this->$relationship;

        if ($related === null) {
            return hash('sha256', $relationship.': null');
        }

        if ($related instanceof Model) {
            return $related instanceof Identifiable
                ? $related->hash
                : $this->identifyModel($related);
        }

        if ($related->isEmpty()) {
            return hash('sha256', $relationship.': []');
        }

        $pivot = $this->identifyingPivotFor($relationship);

        return $related->implode(function (Model $model) use ($pivot) {
            $identity = $model instanceof Identifiable
                ? $model->hash
                : $this->identifyModel($model);

            return $identity.$pivot($model);
        });
    }

    /**
     * Build a resolver that appends a relationship's declared identifying pivot
     * values to each related model's identity. Returns an empty contribution
     * when the relation declares no identifying pivot columns.
     *
     * @return Closure(Model): string
     */
    protected function identifyingPivotFor(string $relationship): Closure
    {
        $relation = $this->$relationship();

        $columns = method_exists($relation, 'identifyingPivotColumns')
            ? Collection::wrap($relation->identifyingPivotColumns())->sort()->values()
            : Collection::make();

        if ($columns->isEmpty()) {
            return fn () => '';
        }

        $accessor = $relation->getPivotAccessor();

        return function (Model $model) use ($columns, $accessor) {
            $pivot = $model->relationLoaded($accessor)
                ? $model->getRelation($accessor)
                : $model->{$accessor};

            if ($pivot === null) {
                return '';
            }

            return $columns
                ->map(fn (string $column) => $column.':'.json_encode($pivot->getAttribute($column)))
                ->implode(', ');
        };
    }

    protected function identifyModel(Model $model): string
    {
        $data = $model->withoutRelations()->toArray();

        unset($data[$model->getKeyName()]);

        if ($model->usesTimestamps()) {
            unset($data[$model->getCreatedAtColumn()]);
            unset($data[$model->getUpdatedAtColumn()]);
        }

        return hash('sha256', json_encode($data));
    }

    protected static function identifyingRelationships(): Collection
    {
        if (! property_exists(static::class, 'identifyingRelationships')) {
            return Collection::make();
        }

        return Collection::wrap(static::$identifyingRelationships)
            ->sort()
            ->values();
    }

    protected static function nonIdentifyingAttributes(): Collection
    {
        return Collection::wrap(static::$nonIdentifyingAttributes ?? [])
            ->sort()
            ->values();
    }
}
