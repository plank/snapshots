<?php

use Plank\Snapshots\Enums\Operation;
use Plank\Snapshots\Models\History;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Flag;

use function Pest\Laravel\artisan;

beforeEach(function () {
    config()->set('snapshots.observers.history', HistoryObserver::class);
});

describe('The visiblity accurately reflects all versions where content is visible', function () {
    /**
     * Create the following situation:
     * Version | Operation     | Visible
     * 1.0.0   | Created       | Yes
     * 1.0.1   | Snapshotted   | Yes
     *         | Updated       | Yes
     * 1.1.0   | Snapshotted   | Yes
     * 2.0.0   | Snapshotted   | No
     *         | SoftDeleted   | No
     * 2.1.0   | Snapshotted   | No
     * 2.2.0   | Snapshotted   | No
     *         | Restored      | Yes
     * 2.2.1   | Snapshotted   | Yes
     *         | Updated       | Yes
     * 3.0.0   | Snapshotted   | Yes
     *         | Deleted       | Yes
     * 4.0.0   | –             | –
     */
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        $flag = Flag::factory()->create();

        // 1.0.0
        createFirstVersion('schema/create');

        $flag->update(['name' => $flag->name.' – patched']);

        // 1.0.1
        createPatchVersion('schema/create');

        // 1.1.0
        createMinorVersion('schema/create');

        $flag->delete();

        // 2.0.0
        createMajorVersion('schema/create');

        // 2.1.0
        createMinorVersion('schema/create');

        $flag->restore();

        // 2.2.0
        createMinorVersion('schema/create');

        $flag->update(['name' => $flag->name.' – patched again']);

        // 2.2.1
        createPatchVersion('schema/create');

        $flag->forceDelete();

        // 3.0.0
        createMajorVersion('schema/create');

        // 4.0.0
        createMajorVersion('schema/create');
    });

    it('shows the correct visibility for each version', function () {
        versions()->setActive(version('1.0.0'));
        $history = Flag::query()->first()->visibleHistory();

        $revision = function (string $number) use ($history): ?History {
            /** @var History $found */
            $found = $history->filter(function (History $item) use ($number) {
                return $item->version->number->isEqualTo($number);
            })->first();

            return $found;
        };

        expect($revision('1.0.0')->operation)->toBe(Operation::Created);
        expect($revision('1.0.1')->operation)->toBe(Operation::Updated);
        expect($revision('1.1.0')->operation)->toBe(Operation::Snapshotted);
        expect($revision('2.0.0')->operation)->toBe(Operation::SoftDeleted);
        expect($revision('2.1.0')->operation)->toBe(Operation::Snapshotted);
        expect($revision('2.2.0')->operation)->toBe(Operation::Restored);
        expect($revision('2.2.1')->operation)->toBe(Operation::Updated);
        expect($revision('3.0.0'))->toBeNull();
        expect($revision('4.0.0'))->toBeNull();
    });
});
