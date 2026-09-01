<?php

use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\User;

use function Pest\Laravel\artisan;

describe('HasMany relationships use snapshotted tables correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();
    });

    it('selects the correct table when saving snapshotted models on non-snapshotted models', function () {
        // In the original content create a user and attach a post
        $user = User::factory()
            ->create();

        $user->posts()
            ->save(Post::factory()->make());

        expect($user->posts()->count())->toBe(1);

        // Create a snapshot and ensure the post was copied over
        snapshots()->setActive(createFirstSnapshot('query'));

        expect($user->posts()->count())->toBe(1);

        // remove the user's post from the first snapshot
        $user->posts()->delete();

        expect($user->posts()->count())->toBe(0);

        snapshots()->clearActive();

        // Go back to the original content and attach another post
        $user->posts()
            ->save(Post::factory()->make());

        expect($user->posts()->count())->toBe(2);

        // Create a new snapshot and verify the attached post matches the previous snapshot
        snapshots()->setActive(createMinorSnapshot('query'));

        expect($user->posts()->count())->toBe(2);

        // Go back to the first snapshot and ensure the post was not attached there
        snapshots()->setActive(snapshots()->byKey('1.0.0'));

        expect($user->posts()->count())->toBe(0);
    });
});
