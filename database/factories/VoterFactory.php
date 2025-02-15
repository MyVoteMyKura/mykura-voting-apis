<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voter>
 */
class VoterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->unique()->phoneNumber,
            'pfNumber' => $this->faker->unique()->randomNumber(5),
            'email' => $this->faker->unique()->safeEmail,
            'email_verified' => $this->faker->boolean,
            'google_id' => $this->faker->unique()->randomNumber(5),
            'picture_url' => $this->faker->imageUrl(),
            'ip_address' => $this->faker->ipv4,
            'inline_url' => $this->faker->url,
            'secret' => $this->faker->password,
        ];
    }
}
