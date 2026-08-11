<?php

use Illuminate\Support\Facades\DB;
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

describe('newHash is derived from the persisted row, not in-memory state', function () {
    it('ignores dirty in-memory attributes until they are persisted', function () {
        $post = Post::query()->where('title', 'Post 1')->first();

        $persisted = $post->newHash();

        // Dirty an identifying attribute in memory without saving it.
        $post->title = 'Only In Memory';

        expect($post->newHash())->toBe($persisted);

        // Once persisted, the hash reflects the new content.
        $post->save();

        expect($post->newHash())->not->toBe($persisted);
    });

    it('is immune to JSON re-encoding of an identifying attribute holding identical data', function () {
        // This is the production scenario: the row is stored with one JSON byte
        // representation, but the in-memory model carries a different encoding of
        // the *same* data (Eloquent's setter re-encodes on assignment). The hash
        // must depend on the stored bytes, not the transient in-memory bytes.
        $post = Post::query()->where('title', 'Post 1')->first();
        $post->update(['meta' => ['b' => 2, 'a' => 1]]);

        // Simulate an external writer that stored equal data with different bytes
        // (extra whitespace) than Eloquent's json_encode would produce.
        DB::table($post->getTable())
            ->where($post->getKeyName(), $post->getKey())
            ->update(['meta' => '{"b": 2, "a": 1}']);

        $fromDb = Post::query()->whereKey($post->getKey())->first()->newHash();

        $mutated = Post::query()->whereKey($post->getKey())->first();

        // Re-assigning the decoded value re-encodes it (whitespace stripped), so
        // the in-memory bytes now differ from what is stored — the exact state
        // that produced a divergent, stale hash before the fresh-read fix.
        $mutated->meta = $mutated->meta;

        // The in-memory bytes now differ from what is stored (whitespace stripped),
        // yet the hash still matches, because it is derived from the stored row.
        expect($mutated->getAttributes()['meta'])->not->toBe('{"b": 2, "a": 1}');
        expect($mutated->newHash())->toBe($fromDb);
    });

    it('returns a stable, non-null string hash when the underlying row is gone', function () {
        $post = Post::query()->where('title', 'Post 1')->first();

        DB::table($post->getTable())
            ->where($post->getKeyName(), $post->getKey())
            ->delete();

        expect($post->newHash())
            ->toBeString()
            ->toBe($post->newHash());
    });
});
