<?php

namespace Plank\Snapshots\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Tests\Models\Like;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\User;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }
}
