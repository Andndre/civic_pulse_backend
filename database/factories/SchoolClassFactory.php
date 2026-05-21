<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Grade '.fake()->randomElement(['10', '11', '12']).'-'.fake()->randomElement(['A', 'B', 'C']),
            'grade' => fake()->randomElement([10, 11, 12]),
            'class_code' => strtoupper(Str::random(8)),
            'homeroom_teacher_id' => User::factory()->state(['role' => 'teacher']),
        ];
    }
}
