<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Casts\AsVersionNumber;
use Plank\Snapshots\Concerns\AsVersion;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Contracts\VersionKey;
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
        'migrated' => 'boolean',
        'copied' => 'boolean',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        if (! $this->hasCast(static::keyColumn())) {
            $this->mergeCasts([
                static::keyColumn() => AsVersionNumber::class,
            ]);
        }

        parent::__construct($attributes);
    }

    public static function boot(): void
    {
        parent::boot();

        static::observe(VersionObserver::class);
    }

    public static function keyColumn(): string
    {
        return 'number';
    }

    public function key(): VersionKey
    {
        return $this->{static::keyColumn()};
    }
}
