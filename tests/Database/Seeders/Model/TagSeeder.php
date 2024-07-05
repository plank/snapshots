<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Model;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::factory()->count(5)->create();

        Post::query()
            ->where('title', 'Post 1')
            ->first()
            ->tags()
            ->attach($tags->take(3));
    }
}
