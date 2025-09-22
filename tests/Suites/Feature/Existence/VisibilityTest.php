<?php

use Plank\Snapshots\Models\Existence;
use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Tests\Models\Flag;

use function Pest\Laravel\artisan;

beforeEach(function () {
    config()->set('snapshots.observers.history', ExistenceObserver::class);
});

describe('The visiblity accurately reflects all versions where content is visible', function () {
    /**
     * Create the following situation:
     * Version | Visible
     * 1.0.0   | Yes
     * 1.0.1   | Yes
     * 1.1.0   | Yes
     * 2.0.0   | No
     * 2.1.0   | No
     * 2.2.0   | Yes
     * 2.2.1   | Yes
     * 3.0.0   | No
     * 4.0.0   | –
     */
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        $flag = Flag::factory()->create();

        // 1.0.0
        createFirstSnapshot('schema/create');

        $flag->update(['name' => $flag->name.' – patched']);

        // 1.0.1
        createPatchSnapshot('schema/create');

        // 1.1.0
        createMinorSnapshot('schema/create');

        $flag->delete();

        // 2.0.0
        createMajorSnapshot('schema/create');

        // 2.1.0
        createMinorSnapshot('schema/create');

        $flag->restore();

        // 2.2.0
        createMinorSnapshot('schema/create');

        $flag->update(['name' => $flag->name.' – patched again']);

        // 2.2.1
        createPatchSnapshot('schema/create');

        $flag->forceDelete();

        // 3.0.0
        createMajorSnapshot('schema/create');

        // 4.0.0
        createMajorSnapshot('schema/create');
    });

    it('shows the correct visibility for each version', function () {
        snapshots()->setActive('1.0.0');
        $existences = Flag::query()->first()->existences;

        $revision = function (string $number) use ($existences): ?Existence {
            /** @var Existence $found */
            $found = $existences->filter(function (Existence $item) use ($number) {
                return $item->snapshot->number->isEqualTo($number);
            })->first();

            return $found;
        };

        expect($revision('1.0.0'))->not->toBeNull();
        expect($revision('1.0.1'))->not->toBeNull();
        expect($revision('1.1.0'))->not->toBeNull();
        expect($revision('2.0.0'))->toBeNull();
        expect($revision('2.1.0'))->toBeNull();
        expect($revision('2.2.0'))->not->toBeNull();
        expect($revision('2.2.1'))->not->toBeNull();
        expect($revision('3.0.0'))->toBeNull();
        expect($revision('4.0.0'))->toBeNull();
    });
});
