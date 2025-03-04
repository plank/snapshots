<?php

namespace Plank\Snapshots\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plank\Snapshots\Tests\Models\Signature;
use Plank\Snapshots\Tests\Models\User;

/**
 * @extends Factory<Signature>
 */
class SignatureFactory extends Factory
{
    protected $model = Signature::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'file' => str($this->faker->words(3, true))->slug()->toString().'.'.$this->faker->randomLetter().$this->faker->randomLetter().$this->faker->randomLetter(),
        ];
    }
}
