<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Enums\Operation;

/**
 * @property string $operation
 * @property string $trackable_type
 * @property string|int $trackable_id
 * @property-read Model&Trackable $trackable
 * @property string|int $version_id
 * @property-read Version|null $version
 * @property array $from
 * @property array $to
 */
class History extends MorphPivot
{
    protected $table = 'history';

    protected $guarded = ['id'];

    protected $casts = [
        'operation' => Operation::class,
        'from' => 'json',
        'to' => 'json',
    ];

    /**
     * Get the Model representing the content that was versioned
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the Model representing the content that was versioned
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the Version associated with the History
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(config()->get('snapshots.models.version'));
    }
}
