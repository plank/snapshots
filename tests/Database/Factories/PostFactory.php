<?php

namespace Plank\Snapshots\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\User;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'user_id' => User::factory(),
            'slug' => str($title)->slug(),
            'title' => $title,
            'body' => fake()->paragraphs(3, true),
        ];
    }
}
