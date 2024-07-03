<?php

use Illuminate\Support\Facades\Event;
use function Pest\Laravel\artisan;
use Plank\Snapshots\Events\TableCopied;
use Plank\Snapshots\Listeners\LabelHistory;
use Plank\Snapshots\Observers\HistoryObserver;
use Plank\Snapshots\Tests\Models\Flag;
use Plank\Snapshots\ValueObjects\Revision;

beforeEach(function () {
    config()->set('snapshots.history.observer', HistoryObserver::class);
    config()->set('snapshots.history.labler', LabelHistory::class);

    Event::listen(TableCopied::class, LabelHistory::class);
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
            '--path' => migrationPath('schema/create_for_model'),
            '--realpath' => true,
        ])->run();

        $flag = Flag::factory()->create();

        // 1.0.0
        createFirstVersion('schema/create_for_model');

        $flag->update(['name' => $flag->name.' – patched']);

        // 1.0.1
        createPatchVersion('schema/create_for_model');

        // 1.1.0
        createMinorVersion('schema/create_for_model');

        $flag->delete();

        // 2.0.0
        createMajorVersion('schema/create_for_model');

        // 2.1.0
        createMinorVersion('schema/create_for_model');

        $flag->restore();

        // 2.2.0
        createMinorVersion('schema/create_for_model');

        $flag->update(['name' => $flag->name.' – patched again']);

        // 2.2.1
        createPatchVersion('schema/create_for_model');

        $flag->forceDelete();

        // 3.0.0
        createMajorVersion('schema/create_for_model');

        // 4.0.0
        createMajorVersion('schema/create_for_model');
    });

    it('shows the correct visibility for each version', function () {
        versions()->setActive(version('1.0.0'));

        /** @var Flag $flag */
        $flag = Flag::query()->first();

        $revision = function (string $number) use ($flag): ?Revision {
            /** @var Revision $found */
            $found = $flag->visibility->filter(function (Revision $revision) use ($number) {
                return $revision->version->number->isEqualTo($number);
            })->first();

            return $found;
        };

        expect($revision('1.0.0')->hidden)->toBeFalse();
        expect($revision('1.0.1')->hidden)->toBeFalse();
        expect($revision('1.1.0')->hidden)->toBeFalse();
        expect($revision('2.0.0')->hidden)->toBeTrue();
        expect($revision('2.1.0')->hidden)->toBeTrue();
        expect($revision('2.2.0')->hidden)->toBeFalse();
        expect($revision('2.2.1')->hidden)->toBeFalse();
        expect($revision('3.0.0'))->toBeNull();
        expect($revision('4.0.0'))->toBeNull();
    });
});
