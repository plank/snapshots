<?php

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;
use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Models\Post;

describe('Versioned models use the version prefixed table when interacting with the database', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed(PostSeeder::class);
    });

    it('can retrieve the correct version of a model', function () {
        // Verify the seeded post was found and has the correct data
        expect(($post = Post::query()->where('title', 'Post 1')->first()))->not->toBeNull();

        // Create a new version and make it active
        versions()->setActive(createFirstVersion('query'));

        // Assert the posts will be querying the new versions tables
        expect((new Post)->getTable())->toBe('v1_0_0_posts');

        // Verify the post was copied over when migrating the version
        expect(Post::find($post->uuid)->title)->toBe('Post 1');
    });

    it('can save a model to the correct version table', function () {
        // Create a new version and make it active
        versions()->setActive(createFirstVersion('query'));

        // Create a new post
        $post = Post::factory()->create(['title' => 'Saved to v1.0.0']);

        // Verify the query will be using the correct table
        expect($post->getTable())->toBe('v1_0_0_posts');

        // Verify the post was saved to the correct table
        expect(Post::query()->where('title', 'Saved to v1.0.0')->exists())->toBeTrue();

        // Switch back to the original content
        versions()->clearActive();

        // Verify the post was not saved to the incorrect table
        expect(Post::query()->where('title', 'Saved to v1.0.0')->exists())->toBeFalse();
    });

    it('can delete a model from the correct version table', function () {
        // Create a new version and make it active
        versions()->setActive(createFirstVersion('query'));

        // Find the first post
        expect(($post = Post::query()->where('title', 'Post 1')->first()))->not->toBeNull();

        // Verify the query will be using the correct table
        expect($post->getTable())->toBe('v1_0_0_posts');

        // Delete the post
        $post->delete();

        // Verify the post was deleted from the correct table
        expect(Post::query()->where('title', 'Post 1')->exists())->toBeFalse();

        // Switch back to the original content
        versions()->clearActive();

        // Verify the post was not deleted from the incorrect table
        expect(Post::query()->where('title', 'Post 1')->exists())->toBeTrue();
    });

    it('can update a model in the correct version table', function () {
        // Create a new version and make it active
        versions()->setActive(createFirstVersion('query'));

        // Find the first post
        expect(($post = Post::query()->where('title', 'Post 1')->first()))->not->toBeNull();

        // Verify the query will be using the correct table
        expect($post->getTable())->toBe('v1_0_0_posts');

        // Fix some errate in the first version
        $post->update(['title' => 'Fixed Errata in v1.0.0']);

        // Ensure the post was updated
        expect(($post = Post::query()->where('title', 'Fixed Errata in v1.0.0')->first()))->not->toBeNull();

        // Fo back to the working version
        versions()->clearActive();

        // Ensure we are back in the working version
        expect($post->getTable())->toBe('posts');

        // Update the post
        $post->update(['title' => 'Updated for v1.0.0']);

        // Verify the post was updated in the correct table
        expect(Post::query()->where('title', 'Updated for v1.0.0')->exists())->toBeTrue();

        // Create a new version and make it active
        versions()->setActive(createPatchVersion('query'));

        // Verify the query will be using the correct table
        expect($post->getTable())->toBe('v1_0_1_posts');

        // Verify the post was carried over from the working version
        expect(Post::query()->where('title', 'Updated for v1.0.0')->exists())->toBeTrue();
    });
});
