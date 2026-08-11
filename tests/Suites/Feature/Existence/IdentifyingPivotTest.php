<?php

use Plank\Snapshots\Observers\ExistenceObserver;
use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Models\Post;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

beforeEach(function () {
    config()->set('snapshots.observers.existence', ExistenceObserver::class);

    artisan('migrate', [
        '--path' => migrationPath('query'),
        '--realpath' => true,
    ])->run();

    seed(PostSeeder::class);
});

/**
 * Post::related() is an identifying belongsToMany declaring
 * ->withIdentifyingPivot(['note']) over pivot columns ['note', 'weight'].
 */
describe('declared identifying pivot columns participate in the parent hash', function () {
    it('moves the parent hash when a declared identifying pivot column changes', function () {
        $post = Post::query()->where('title', 'Post 1')->first();
        $related = Post::query()->where('title', 'Post 2')->first();

        $before = $post->hash;

        expect($before)->not->toBeNull();

        $post->related()->updateExistingPivot($related->getKey(), ['note' => 'a freshly written note']);

        expect($post->hash)->not->toBe($before);
    });

    it('leaves the parent hash unchanged when an undeclared pivot column changes', function () {
        $post = Post::query()->where('title', 'Post 1')->first();
        $related = Post::query()->where('title', 'Post 2')->first();

        $before = $post->hash;

        // 'weight' is a withPivot column but is not declared identifying, so a
        // recompute still fires (via updateExistingPivot) but the value must not move.
        $post->related()->updateExistingPivot($related->getKey(), ['weight' => 42]);

        expect($post->hash)->toBe($before);
    });

    it('distinguishes different declared pivot values', function () {
        $post = Post::query()->where('title', 'Post 1')->first();
        $related = Post::query()->where('title', 'Post 2')->first();

        $post->related()->updateExistingPivot($related->getKey(), ['note' => 'first']);
        $first = $post->hash;

        $post->related()->updateExistingPivot($related->getKey(), ['note' => 'second']);
        expect($post->hash)->not->toBe($first);

        $post->related()->updateExistingPivot($related->getKey(), ['note' => 'first']);
        expect($post->hash)->toBe($first);
    });
});
