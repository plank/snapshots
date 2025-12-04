<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;

/**
 * @property string $trackable_type
 * @property string|int $trackable_id
 * @property string|int $version_id
 * @property ?string $hash
 * @property-read Model&Trackable $trackable
 * @property-read Version|null $version
 */
class Existence extends MorphPivot
{
    protected $table = 'existences';

    public $incrementing = true;

    protected $guarded = [];

    protected $casts = [];

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(config()->get('snapshots.models.version'));
    }

    public static function createOrUpdateFor(Versioned&Model $model, (Version&Model)|null $version): self
    {
        if ($existence = $model->existences()->where(static::versionColumn(), $version?->getKey())->first()) {
            if ($model instanceof Identifiable) {
                $existence->hash = $model->newHash();
                $existence->save();
            }

            return $existence;
        }

        return static::query()->create([
            'trackable_type' => $model::class,
            'trackable_id' => $model->getKey(),
            'version_id' => $version?->getKey(),
            'last_changed_in' => $version?->getKey(),
            'hash' => $model instanceof Identifiable ? $model->newHash() : null,
        ]);
    }

    public static function copiedTo(Versioned&Model $model, Version&Model $version): self
    {
        $existence = $model->existence->replicate();
        $existence->version_id = $version->id;
        $existence->save();

        return $existence;
    }

    public static function versionColumn(): string
    {
        return 'version_id';
    }
}
