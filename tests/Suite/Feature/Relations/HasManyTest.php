<?php

use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\User;

use function Pest\Laravel\artisan;

describe('HasMany relationships use versioned tables correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();
    });

    it('selects the correct table when saving versioned models on non-versioned models', function () {
        // In the original content create a user and attach a post
        $user = User::factory()
            ->create();

        $user->posts()
            ->save(Post::factory()->make());

        expect($user->posts()->count())->toBe(1);

        // Create a version and ensure the post was copied over
        versions()->setActive(createFirstVersion('query'));

        expect($user->posts()->count())->toBe(1);

        versions()->clearActive();

        // Go back to the original content and attach another post
        $user->posts()
            ->save(Post::factory()->make());

        expect($user->posts()->count())->toBe(2);

        // Create a new version and verify the attached post matches the previous version 
        versions()->setActive(releaseAndCreateMinorVersion('query'));

        expect($user->posts()->count())->toBe(1);

        // Go back to the first version and ensure the post was not attached there
        versions()->setActive(versions()->byNumber('1.0.0'));

        expect($user->posts()->count())->toBe(1);
    });
});
