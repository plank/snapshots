<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsVersion;
use Plank\Snapshots\Concerns\HasVersionNumber;
use Plank\Snapshots\Contracts\Version as VersionContract;
use Plank\Snapshots\Contracts\VersionKey;
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
    use HasVersionNumber;

    protected $guarded = [];

    protected $casts = [
        'migrated' => 'boolean',
        'copied' => 'boolean',
    ];

    public static function keyColumn(): string
    {
        return 'number';
    }

    public function key(): VersionKey
    {
        return $this->{static::keyColumn()};
    }
}
