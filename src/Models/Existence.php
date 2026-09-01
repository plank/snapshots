<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Snapshots\Contracts\Identifiable;
use Plank\Snapshots\Contracts\Snapshotted;
use Plank\Snapshots\Contracts\Trackable;

/**
 * @property string $trackable_type
 * @property string|int $trackable_id
 * @property string|int $snapshot_id
 * @property ?string $hash
 * @property-read Model&Trackable $trackable
 * @property-read Snapshot|null $snapshot
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

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(config()->get('snapshots.models.snapshot'));
    }

    public static function createOrUpdateFor(Snapshotted&Model $model, (Snapshot&Model)|null $snapshot): self
    {
        if ($existence = $model->existences()->where(static::snapshotColumn(), $snapshot?->getKey())->first()) {
            if ($model instanceof Identifiable) {
                $existence->hash = $model->newHash();
                $existence->save();
            }

            return $existence;
        }

        return static::query()->create([
            'trackable_type' => $model::class,
            'trackable_id' => $model->getKey(),
            'snapshot_id' => $snapshot?->getKey(),
            'last_changed_in' => $snapshot?->getKey(),
            'hash' => $model instanceof Identifiable ? $model->newHash() : null,
        ]);
    }

    public static function copiedTo(Snapshotted&Model $model, Snapshot&Model $snapshot): self
    {
        $existence = $model->existence->replicate();
        $existence->snapshot_id = $snapshot->id;
        $existence->save();

        return $existence;
    }

    public static function snapshotColumn(): string
    {
        return 'snapshot_id';
    }
}
