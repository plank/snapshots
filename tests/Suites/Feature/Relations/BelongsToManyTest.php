<?php

use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Models\Post;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('BelongsToMany relationships use snapshotted tables when one of the models is snapshotted', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed(PostSeeder::class);
    });

    it('can attach snapshotted models to snapshotted models', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        // Ensure the posts are not yet related to eachother
        expect($post1->related->pluck('uuid'))->not()->toContain($post2->uuid);

        // Relate the posts to eachother in the next snapshot
        snapshots()->setActive(createFirstSnapshot('query'));

        $post1 = $post1->activeSnapshot();
        $post1->related()->attach($post2);
        expect($post1->related->first()->uuid)->toBe($post2->uuid);

        // Ensure the posts are still not related in the working copy
        snapshots()->clearActive();

        expect($post1->activeSnapshot()->related->pluck('uuid'))->not()->toContain($post2->uuid);
    });

    it('can detach snapshotted models to snapshotted models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        $post2 = $post1->related->where('title', 'Post 2')->first();

        $post1->related()->detach($post2);
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(1);
        expect($post1->related->pluck('title'))->not()->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next snapshot
        snapshots()->setActive(createFirstSnapshot('query'));

        $post1 = $post1->activeSnapshot();
        expect($post1->related)->toHaveCount(1);
        expect($post1->related->pluck('title'))->not()->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
    });

    it('can delete the pivot for snapshotted models to snapshotted models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next snapshot
        snapshots()->setActive(createFirstSnapshot('query'));

        $post1 = $post1->activeSnapshot();
        $post1->related()->where('title', 'Post 2')->first()->pivot->delete();
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(1);
        expect($post1->related->pluck('title'))->not()->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Ensure the posts are still related in the original content
        snapshots()->clearActive();

        $post1 = $post1->activeSnapshot();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
    });

    it('can sync snapshotted models to snapshotted models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next snapshot
        snapshots()->setActive(createFirstSnapshot('query'));

        $post4 = Post::factory()->create([
            'title' => 'Post 4',
            'body' => 'Post 4 body',
        ]);

        $post1 = $post1->activeSnapshot();
        $post1->related()->sync($post1->related->pluck('uuid')->push($post4->uuid));
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(3);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->toContain('Post 4');

        // Ensure the posts are still related in the original content
        snapshots()->clearActive();

        $post1 = $post1->activeSnapshot();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->not()->toContain('Post 4');
    });

    it('can sync without detaching snapshotted models to snapshotted models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next snapshot
        snapshots()->setActive(createFirstSnapshot('query'));

        $post4 = Post::factory()->create([
            'title' => 'Post 4',
            'body' => 'Post 4 body',
        ]);

        $post1 = $post1->activeSnapshot();
        $post1->related()->syncWithoutDetaching($post1->related->pluck('uuid')->push($post4->uuid));
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(3);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->toContain('Post 4');

        // Ensure the posts are still related in the original content
        snapshots()->clearActive();

        $post1 = $post1->activeSnapshot();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->not()->toContain('Post 4');
    });
});
