<?php

namespace Plank\Snapshots\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsVersion;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Database\Factories\VersionFactory;
use Plank\Snapshots\Observers\VersionObserver;
use Plank\Snapshots\ValueObjects\VersionNumber;

/**
 * @property VersionNumber $number
 * @property Carbon $released_at
 * @property bool $migrated
 * @property Version $previous
 */
class Version extends Model implements VersionContract
{
    use HasFactory;
    use AsVersion;

    public bool $releasing = false;

    public bool $unreleasing = false;

    protected $casts = [
        'number' => \Plank\Snapshots\Casts\VersionNumber::class,
        'released_at' => 'datetime',
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
        return VersionFactory::new();
    }

    /**
     * Determine if the Version has been released.
     */
    public function isReleased(): bool
    {
        /** @var Carbon $date */
        $date = $this->getOriginal('released_at');

        return $date !== null && $date->isPast();
    }

    /**
     * Set the Version's state to reflect it is about to be released
     */
    public function beginRelease(): void
    {
        $this->releasing = true;
        $this->unreleasing = false;
    }

    /**
     * Perform the Release of the version
     */
    public function release(): void
    {
        $this->released_at = now();
        $this->save();
        $this->releasing = false;
    }

    /**
     * Determine if the Version was recently released
     */
    public function wasRecentlyUnreleased(): bool
    {
        return $this->unreleasing;
    }

    /**
     * Set the Version's state to reflect it is about to be unreleased
     */
    public function beginUnrelease(): void
    {
        $this->releasing = false;
        $this->unreleasing = true;
    }

    /**
     * Perform the Unrelease of the version
     */
    public function unrelease(): void
    {
        $this->released_at = null;
        $this->save();
        $this->unreleasing = false;
    }

    /**
     * Determine if the Version was recently unreleased
     */
    public function wasRecentlyReleased(): bool
    {
        return $this->releasing;
    }

    public function uriKey(): string
    {
        return (string) $this->number;
    }
}
