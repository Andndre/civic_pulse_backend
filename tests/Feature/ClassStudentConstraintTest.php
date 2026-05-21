<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClassStudentConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_join_multiple_classes_in_same_grade(): void
    {
        // 1. Create a student user
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Student Test',
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create classes in grade 7 (7A and 7B)
        $class7AId = DB::table('classes')->insertGetId([
            'name' => 'Class 7A',
            'grade' => 7,
            'class_code' => 'CODE-7A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $class7BId = DB::table('classes')->insertGetId([
            'name' => 'Class 7B',
            'grade' => 7,
            'class_code' => 'CODE-7B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Student joins Class 7A (Grade 7) - Should succeed
        DB::table('class_student')->insert([
            'student_id' => $studentId,
            'class_id' => $class7AId,
            'grade' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $studentId,
            'class_id' => $class7AId,
            'grade' => 7,
        ]);

        // 4. Student tries to join Class 7B (Grade 7) - Should fail due to unique constraint on (student_id, grade)
        $this->expectException(QueryException::class);

        DB::table('class_student')->insert([
            'student_id' => $studentId,
            'class_id' => $class7BId,
            'grade' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_student_can_join_classes_in_different_grades(): void
    {
        // 1. Create a student user
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Student Test',
            'email' => 'student2@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create class in grade 7 (7A) and grade 8 (8A)
        $class7AId = DB::table('classes')->insertGetId([
            'name' => 'Class 7A',
            'grade' => 7,
            'class_code' => 'CODE-7A-ALT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $class8AId = DB::table('classes')->insertGetId([
            'name' => 'Class 8A',
            'grade' => 8,
            'class_code' => 'CODE-8A-ALT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Student joins Class 7A (Grade 7) - Should succeed
        DB::table('class_student')->insert([
            'student_id' => $studentId,
            'class_id' => $class7AId,
            'grade' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Student joins Class 8A (Grade 8) - Should succeed
        DB::table('class_student')->insert([
            'student_id' => $studentId,
            'class_id' => $class8AId,
            'grade' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $studentId,
            'class_id' => $class7AId,
            'grade' => 7,
        ]);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $studentId,
            'class_id' => $class8AId,
            'grade' => 8,
        ]);
    }

    public function test_class_student_grade_must_match_class_grade(): void
    {
        // 1. Create a student user
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Student Test',
            'email' => 'student3@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create class in grade 7 (7A)
        $class7AId = DB::table('classes')->insertGetId([
            'name' => 'Class 7A',
            'grade' => 7,
            'class_code' => 'CODE-7A-CHECK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Try to join class 7A but specifying grade 8 - Should fail foreign key check
        $this->expectException(QueryException::class);

        DB::table('class_student')->insert([
            'student_id' => $studentId,
            'class_id' => $class7AId,
            'grade' => 8, // Wrong grade for Class 7A
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
