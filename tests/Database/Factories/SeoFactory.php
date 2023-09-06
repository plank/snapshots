<?php

namespace Plank\Snapshots\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Tests\Models\Like;
use Plank\Snapshots\Tests\Models\Post;
use Plank\Snapshots\Tests\Models\Seo;

/**
 * @extends Factory<Like>
 */
class SeoFactory extends Factory
{
    protected $model = Seo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attribute = $this->faker->randomElement(['title', 'description', 'keywords']);

        $value = '';

        switch ($attribute) {
            case 'title':
                $value = $this->faker->sentence;
                break;
            case 'description':
                $value = $this->faker->paragraph;
                break;
            case 'keywords':
                $value = $this->faker->words(5, true);
                break;
        }

        return [
            'post_id' => Post::factory(),
            'attribute' => $attribute,
            'value' => $value,
        ];
    }
}
