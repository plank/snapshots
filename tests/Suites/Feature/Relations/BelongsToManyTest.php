<?php

use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Models\Post;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('BelongsTo relationships use versioned tables when one of the models is versioned', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed(PostSeeder::class);
    });

    it('can attach versioned models to versioned models', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        // Ensure the posts are not yet related to eachother
        expect($post1->related->pluck('uuid'))->not()->toContain($post2->uuid);

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post1 = $post1->activeVersion();
        $post1->related()->attach($post2);
        expect($post1->related->first()->uuid)->toBe($post2->uuid);

        // Ensure the posts are still not related in the working copy
        versions()->clearActive();

        expect($post1->activeVersion()->related->pluck('uuid'))->not()->toContain($post2->uuid);
    });

    it('can detach versioned models to versioned models', function () {
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

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post1 = $post1->activeVersion();
        expect($post1->related)->toHaveCount(1);
        expect($post1->related->pluck('title'))->not()->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
    });

    it('can delete the pivot for versioned models to versioned models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post1 = $post1->activeVersion();
        $post1->related()->where('title', 'Post 2')->first()->pivot->delete();
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(1);
        expect($post1->related->pluck('title'))->not()->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Ensure the posts are still related in the original content
        versions()->clearActive();

        $post1 = $post1->activeVersion();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
    });

    it('can sync versioned models to versioned models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post4 = Post::factory()->create([
            'title' => 'Post 4',
            'body' => 'Post 4 body',
        ]);

        $post1 = $post1->activeVersion();
        $post1->related()->sync($post1->related->pluck('uuid')->push($post4->uuid));
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(3);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->toContain('Post 4');

        // Ensure the posts are still related in the original content
        versions()->clearActive();

        $post1 = $post1->activeVersion();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->not()->toContain('Post 4');
    });

    it('can sync without detaching versioned models to versioned models', function () {
        $post1 = Post::query()->where('title', 'Post 1')->first();

        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post4 = Post::factory()->create([
            'title' => 'Post 4',
            'body' => 'Post 4 body',
        ]);

        $post1 = $post1->activeVersion();
        $post1->related()->syncWithoutDetaching($post1->related->pluck('uuid')->push($post4->uuid));
        $post1->unsetRelation('related');

        expect($post1->related)->toHaveCount(3);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->toContain('Post 4');

        // Ensure the posts are still related in the original content
        versions()->clearActive();

        $post1 = $post1->activeVersion();
        expect($post1->related)->toHaveCount(2);
        expect($post1->related->pluck('title'))->toContain('Post 2');
        expect($post1->related->pluck('title'))->toContain('Post 3');
        expect($post1->related->pluck('title'))->not()->toContain('Post 4');
    });
});
