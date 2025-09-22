<?php

use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Database\Seeders\Model\TagSeeder;
use Plank\Snapshots\Tests\Database\Seeders\Model\VideoSeeder;
use Plank\Snapshots\Tests\Models\Post;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

beforeEach(function () {
    config()->set('snapshots.observers.existence', ExistenceObserver::class);
});

describe('Identity is accurately tracked across versions and relationships', function () {
    beforeEach(function () {
        config()->set('snapshots.observers.history', ExistenceObserver::class);

        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed(PostSeeder::class);
        seed(TagSeeder::class);
        seed(VideoSeeder::class);
    });

    /**
     * Post:
     * $identifyingRelationships = ['tags', 'related', 'videos'];
     * $identifiesRelationships = ['associated'];
     * $nonIdentifyingAttributes = ['updated_at'];
     */
    it('can track identity across relations', function () {
        $post1 = Post::query()
            ->where('title', 'Post 1')
            ->first();

        $post2 = Post::query()
            ->where('title', 'Post 2')
            ->first();

        $tag = $post1->tags()->first();
        $video = $post1->videos()->first();

        expect($hash1a = $post1->hash)->not->toBeNull();
        expect($hash2a = $post2->hash)->not->toBeNull();

        // Verify updating the model updates its hash
        $post1->update(['title' => 'Post 1 Updated']);
        expect($hash1b = $post1->hash)->not->toBe($hash1a);
        expect($post2->hash)->toBe($hash2a);

        // Verify updating a related post updates its hash
        $post2->update(['title' => 'Post 2 Updated']);
        expect($hash1c = $post1->hash)->not->toBe($hash1b);
        expect($hash2b = $post2->hash)->not->toBe($hash2a);

        // Verify updating a non-identifiable item updates its identifying hashes
        $tag->update(['name' => 'Tag Updated']);
        expect($hash1d = $post1->hash)->not->toBe($hash1c);
        expect($post2->hash)->toBe($hash2b);

        // Verify detaching identifying relationships updates the hash
        $tag->posts()->detach($post1);
        expect($hash1e = $post1->hash)->not->toBe($hash1d);

        $video->delete();
        expect($hash1f = $post1->hash)->not->toBe($hash1e);

        $video->restore();
        expect($hash1g = $post1->hash)->not->toBe($hash1f);

        $video->forceDelete();
        expect($post1->hash)->not->toBe($hash1g);
    });

    it('does not update the hash when a non-identifying attribute is changed', function () {
        $post = Post::query()
            ->where('title', 'Post 1')
            ->with('existence')
            ->first();

        expect($hash = $post->hash)->not->toBeNull();

        $post->touch();

        expect($post->hash)->toBe($hash);
    });
});
