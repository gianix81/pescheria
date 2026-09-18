<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+39 3## ######'),
            'role' => Role::BUYER,
            'store_id' => null,
            'is_active' => true,
            'must_change_password' => false,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function buyer(): static
    {
        return $this->state(fn () => ['role' => Role::BUYER, 'store_id' => null]);
    }

    public function tecnico(): static
    {
        return $this->state(fn () => ['role' => Role::TECNICO, 'store_id' => null]);
    }

    public function capoReparto(?Store $store = null): static
    {
        return $this->state(fn () => [
            'role' => Role::CAPO_REPARTO,
            'store_id' => $store?->id ?? Store::factory(),
        ]);
    }

    public function inattivo(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
