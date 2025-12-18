<?php

namespace Plank\Snapshots\Concerns;

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
        $identity = $this->modelHash();

        $identity .= static::identifyingRelationships()
            ->implode(fn (string $relationship) => $this->relatedHash($relationship), '');

        return hash('sha256', $identity);
    }

    public function modelHash(): string
    {
        $hidden = $this->getHidden();
        $visible = $this->getVisible();

        $identity = Collection::make($this->attributes)
            ->except(static::nonIdentifyingAttributes())
            ->except($hidden)
            ->when($visible, fn ($attributes) => $attributes->only($visible))
            ->sortKeys()
            ->map(fn ($value, $key) => $key.':'.json_encode($value))
            ->implode(', ');

        return hash('sha256', $identity);
    }

    protected function relatedHash(string $relationship): string
    {
        // We don't want to alter the state of which relations are eager loaded, to leave
        // a minimal footprint on consuming applications
        $related = $this->relationLoaded($relationship)
            ? Collection::wrap($this->unsetRelation($relationship)->$relationship)
            : $this->$relationship()->get();

        if ($related->isEmpty()) {
            return hash('sha256', $relationship.': []');
        }

        return $related->implode(function (Model $model) {
            if ($model instanceof Identifiable) {
                return $model->modelHash();
            }

            return $this->identifyModel($model);
        });
    }

    protected function identifyModel(Model $model): string
    {
        $data = $model->withoutRelations()->toArray();

        unset($data[$model->getKey()]);

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
