<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use App\Models\TestResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestResponse>
 */
class TestResponseFactory extends Factory
{
    protected $model = TestResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'learning_material_id' => LearningMaterial::factory(),
            'type' => fake()->randomElement(['pre_test', 'post_test']),
            'score' => fake()->numberBetween(50, 100),
        ];
    }
}
