<?php

namespace Plank\Snapshots\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsSnapshot;
use Plank\Snapshots\Concerns\HasVersionNumber;
use Plank\Snapshots\Contracts\Snapshot as SnapshotContract;
use Plank\Snapshots\Contracts\VersionKey;
use Plank\Snapshots\ValueObjects\VersionNumber;

/**
 * @property VersionNumber $number
 * @property bool $migrated
 * @property Snapshot $previous
 */
class Snapshot extends Model implements SnapshotContract
{
    use AsSnapshot;
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
