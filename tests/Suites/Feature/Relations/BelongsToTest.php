<?php

use Plank\Snapshots\Tests\Database\Seeders\Model\PostSeeder;
use Plank\Snapshots\Tests\Models\Like;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\Seo;
use Plank\Snapshots\Tests\Models\User;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

describe('BelongsTo relationships use snapshotted tables when one of the models is snapshotted', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('query'),
            '--realpath' => true,
        ])->run();

        seed(PostSeeder::class);
    });

    it('can make a snapshotted model belong to a non snapshotted model', function () {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        expect($post->user->id)->toBe($user->id);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($post->activeSnapshot()->user->id)->toBe($user->id);
    });

    it('can update a belongs to relationship to a non snapshotted model from a snapshotted model', function () {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        expect($post->user->id)->not()->toBe($user->id);

        $post->user()->associate($user)->save();

        expect($post->user->id)->toBe($user->id);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($post->activeSnapshot()->user->id)->toBe($user->id);
    });

    it('can make a non snapshotted model belong to a snapshotted model', function () {
        $post = Post::factory()->create();
        $like = Like::factory()->create(['post_id' => $post->uuid]);

        expect($like->post->uuid)->toBe($post->uuid);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($like->post()->first()->title)->toBe($post->title);
        expect($post->activeSnapshot()->likes->first()->id)->toBe($like->id);
    });

    it('can update a belongs to relationship to a snapshotted model from a non snapshotted model', function () {
        $post = Post::factory()->create([
            'title' => 'Gets Likes',
        ]);

        $like = Like::factory()->create();

        expect($like->post->uuid)->not()->toBe($post->uuid);

        $like->post()->associate($post)->save();

        expect($like->post->uuid)->toBe($post->uuid);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($like->post->title)->toBe('Gets Likes');
    });

    it('can make a snapshotted model belong to a snapshotted model', function () {
        $post = Post::factory()->create();
        $seo = $post->seos()->create(Seo::factory()->make()->toArray());

        expect($seo->post->uuid)->toBe($post->uuid);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($seo->activeSnapshot()->post->title)->toBe($post->title);
    });

    it('can update a belongs to relationship to a snapshotted model from a snapshotted model', function () {
        $post = Post::factory()->create([
            'title' => 'Gets Seo',
        ]);

        $seo = Seo::factory()->create();

        expect($seo->post->uuid)->not()->toBe($post->uuid);

        $seo->post()->associate($post)->save();

        expect($seo->post->uuid)->toBe($post->uuid);

        snapshots()->setActive(createFirstSnapshot('query'));

        expect($seo->post->title)->toBe('Gets Seo');
    });
});
