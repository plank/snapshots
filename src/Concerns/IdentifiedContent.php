<?php

namespace Plank\Snapshots\Concerns;

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

    public function getHashAttribute(): ?string
    {
        return $this->existence?->hash;
    }

    public function updateHash(): void
    {
        $existence = $this->existence;

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

        return hash('sha-256', $identity);
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

        return hash('sha-256', $identity);
    }

    protected function relatedHash(string $relationship): string
    {
        $result = Collection::wrap($this->unsetRelation($relationship)->$relationship);

        if ($result->isEmpty()) {
            return hash('sha-256', $relationship.': []');
        }

        return $result->implode(function (Model $model) {
            if ($model instanceof Identifiable) {
                return $model->modelHash();
            }

            return hash('sha-256', $model->toJson());
        });
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
