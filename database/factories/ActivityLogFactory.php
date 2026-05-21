<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['competition', 'sports', 'arts', 'volunteer', 'other']),
            'date' => fake()->date('Y-m-d'),
            'location' => fake()->city(),
            'achievement' => fake()->randomElement(['First Place', 'Participant', 'Gold Medal']),
            'points' => fake()->numberBetween(10, 100),
            'evidence_url' => fake()->url(),
            'status' => 'pending',
            'review_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
