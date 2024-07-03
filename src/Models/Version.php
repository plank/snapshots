<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Casts\AsVersionNumber;
use Plank\Snapshots\Concerns\AsVersion;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Observers\VersionObserver;
use Plank\Snapshots\ValueObjects\VersionNumber;

/**
 * @property VersionNumber $number
 * @property bool $migrated
 * @property Version $previous
 */
class Version extends Model implements VersionContract
{
    use AsVersion;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'number' => AsVersionNumber::class,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::observe(VersionObserver::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return config()->get('snapshots.factories.version')::new();
    }

    public function uriKey(): string
    {
        return (string) $this->number;
    }
}
