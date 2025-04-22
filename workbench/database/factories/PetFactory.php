<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Pet;

/**
 * @template TModel of \Workbench\App\Pet
 *
 * @extends Factory<TModel>
 */
class PetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Pet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
          'name' => fake()->name(),
          'type' => fake()->randomElement(['dog', 'cat', 'fish']),
          'owner_id' => UserFactory::new()
        ];
    }
}
