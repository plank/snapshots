<?php

use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Database\Seeders\Model\TagSeeder;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\Tag;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('MorphToMany relationships use versioned tables when one of the models is versioned', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed([
            PostSeeder::class,
            TagSeeder::class,
        ]);
    });

    it('can attach unversioned models to versioned models', function () {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        // Ensure the post and tag are not yet related to eachother
        expect($post->tags->pluck('id'))->not()->toContain($tag->id);

        // Relate the posts to eachother in the next version
        versions()->setActive(createFirstVersion('query'));

        $post = $post->activeVersion();
        $post->tags()->attach($tag);

        // Ensure the post and tag are now related in the version
        expect($post->tags->pluck('id'))->toContain($tag->id);

        // Ensure the posts are still not related in the working copy
        versions()->clearActive();

        expect($post->activeVersion()->tags->pluck('id'))->not()->toContain($tag->id);
    });

    it('can detach unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('query'));

        $post = Post::query()->whereHas('tags')->first();
        expect($post->tags->count())->toBe(3);

        $toDetach = $post->tags->first();
        $post->tags()->detach($toDetach->id);
        $post->unsetRelation('tags');

        expect($post->tags->count())->toBe(2);
        expect($post->tags->pluck('id'))->not()->toContain($toDetach->id);

        versions()->clearActive();

        expect($post->activeVersion()->tags->count())->toBe(3);
        expect($post->activeVersion()->tags->pluck('id'))->toContain($toDetach->id);
    });

    it('can delete the pivot for unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('query'));

        $post = Post::query()->whereHas('tags')->first();
        expect($post->tags->count())->toBe(3);

        $toDetach = $post->tags->first();
        $toDetach->pivot->delete();
        $post->unsetRelation('tags');

        expect($post->tags->count())->toBe(2);
        expect($post->tags->pluck('id'))->not()->toContain($toDetach->id);

        versions()->clearActive();

        expect($post->activeVersion()->tags->count())->toBe(3);
        expect($post->activeVersion()->tags->pluck('id'))->toContain($toDetach->id);
    });

    it('can sync unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('query'));

        /** @var Post $post */
        $post = Post::query()->whereHas('tags')->first();
        $tags = $post->tags;

        $detaching = $tags->take(2);
        $keeping = $tags->last();

        expect($tags->count())->toBe(3);

        $toSync = Tag::factory()->count(2)->create()->push($keeping);
        $post->tags()->sync($toSync->pluck('id'));
        $post->unsetRelation('tags');

        expect($post->tags->count())->toBe(3);
        expect($post->tags->pluck('id'))->not()->toContain($detaching->pluck('id'));
        expect($post->tags->pluck('id'))->toContain(...$toSync->pluck('id')->toArray());
        expect($toSync->first()->posts->pluck('uuid'))->toContain($post->uuid);

        versions()->clearActive();

        expect($post->activeVersion()->tags->count())->toBe(3);
        expect($post->activeVersion()->tags->pluck('id'))->toContain(...$tags->pluck('id')->toArray());
    });

    it('can sync without detaching unversioned models to versioned models', function () {
        versions()->setActive(createFirstVersion('query'));

        /** @var Post $post */
        $post = Post::query()->whereHas('tags')->first();
        $seeded = $post->tags;
        expect($seeded->count())->toBe(3);

        $toSync = Tag::factory()->count(2)->create();
        $post->tags()->syncWithoutDetaching($toSync->pluck('id'));
        $post->unsetRelation('tags');

        expect($post->tags->count())->toBe(5);
        expect($post->tags->pluck('id'))->toContain(...$seeded->pluck('id')->toArray());
        expect($post->tags->pluck('id'))->toContain(...$toSync->pluck('id')->toArray());

        versions()->clearActive();

        expect($post->activeVersion()->tags->count())->toBe(3);
        expect($post->activeVersion()->tags->pluck('id'))->toContain(...$seeded->pluck('id')->toArray());
        expect($post->activeVersion()->tags->pluck('id'))->not()->toContain(...$toSync->pluck('id')->toArray());
    });
});
