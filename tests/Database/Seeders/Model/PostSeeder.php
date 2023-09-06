<?php

namespace Plank\Snapshots\Tests\Database\Seeders\Model;

use Illuminate\Database\Seeder;
use Plank\Snapshots\Tests\Models\Post;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $post1 = Post::factory()->create([
            'title' => 'Post 1',
            'body' => 'Post 1 body',
        ]);

        $post2 = Post::factory()->create([
            'title' => 'Post 2',
            'body' => 'Post 2 body',
        ]);

        $post3 = Post::factory()->create([
            'title' => 'Post 3',
            'body' => 'Post 3 body',
        ]);

        $post1->related()->attach($post2);
        $post1->related()->attach($post3);
    }
}
