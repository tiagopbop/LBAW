<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AuthenticatedUser; // Import your model


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AuthenticatedUserFactory extends Factory
{    protected $model = AuthenticatedUser::class; // Correctly define the model

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(), // Generates a unique username
            'email' => fake()->unique()->safeEmail(),   // Generates a unique email
            'password' => Hash::make('password'),       // Default hashed password
            'user_creation_date' => now()->toDateString(), // Current date as user creation date
            'suspended_status' => false,                // Default suspended status (not suspended)
            'pfp' => fake()->imageUrl(),                // Random profile picture URL
            'pronouns' => fake()->randomElement(['he/him', 'she/her', 'they/them', 'other']), // Random pronouns
            'bio' => fake()->sentence(),                // Random bio sentence
            'country' => fake()->country(),             // Random country
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null, // Email will be unverified
        ]);
    }
}