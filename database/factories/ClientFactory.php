<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
          'clientId' => random_int(1,500),
          'baseUrl' => Str::random(10),
          'host' => Str::random(10),
          'companyName' => fake()->company(),
          'devicesLastUpdate' => now(),
          'eventsLastUpdate' => now(),
          'created_at' => now(),
          'updated_at' => now(),
        ];
    }
}
