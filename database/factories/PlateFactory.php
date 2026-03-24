<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Plate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plate>
 */
class PlateFactory extends Factory
{
    protected $model = Plate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 100),
            'image' => null,
            'is_available' => true,
            'category_id' => Category::factory(),
            'user_id' => User::factory()->admin(),
        ];
    }
}
