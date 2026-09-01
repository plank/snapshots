<?php

namespace Plank\Snapshots\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Models\Snapshot;

class SnapshotFactory extends Factory
{
    protected $model = Snapshot::class;

    public function definition()
    {
        $next = '1.0.0';

        if ($previousId = Snapshot::query()->max('id')) {
            $previous = Snapshot::find($previousId);

            match (random_int(1, 3)) {
                1 => $next = $previous->number->nextMajor(),
                2 => $next = $previous->number->nextMinor(),
                3 => $next = $previous->number->nextPatch(),
            };
        }

        return [
            'previous_snapshot_id' => $previousId,
            'number' => $next,
            'migrated' => false,
            'copied' => false,
        ];
    }
}
