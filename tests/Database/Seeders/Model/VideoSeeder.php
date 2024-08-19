<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Model;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\Video;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $post = Post::query()
            ->where('title', 'Post 1')
            ->first();

        Video::factory()->create([
            'post_id' => $post->uuid,
            'name' => 'Video 1',
        ]);
    }
}
