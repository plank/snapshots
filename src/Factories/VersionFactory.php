<?php

namespace Plank\Snapshots\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Models\Version;

class VersionFactory extends Factory
{
    protected $model = Version::class;

    public function definition()
    {
        $next = '1.0.0';

        if ($previousId = Version::query()->max('id')) {
            $previous = Version::find($previousId);

            match (random_int(1, 3)) {
                1 => $next = $previous->number->nextMajor(),
                2 => $next = $previous->number->nextMinor(),
                3 => $next = $previous->number->nextPatch(),
            };
        }

        return [
            'previous_version_id' => $previousId,
            'number' => $next,
            'migrated' => false,
            'released_at' => null,
        ];
    }
}
