<?php

namespace Database\Factories;

use App\Models\AnecdotalNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnecdotalNote>
 */
class AnecdotalNoteFactory extends Factory
{
    protected $model = AnecdotalNote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'teacher_id' => User::factory()->state(['role' => 'teacher']),
            'content' => fake()->paragraph(),
        ];
    }
}
