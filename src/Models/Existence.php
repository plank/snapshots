<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Snapshots\Contracts\Trackable;

/**
 * @property string $trackable_type
 * @property string|int $trackable_id
 * @property string|int $version_id
 * @property ?string $hash
*
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
}
