<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JoinClassTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student can join a class using a valid class code.
     */
    public function test_student_can_join_class_with_valid_code(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::factory()->create(['grade' => 10]);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully joined the class',
            ])
            ->assertJsonPath('data.id', $class->id)
            ->assertJsonPath('data.name', $class->name);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'grade' => 10,
        ]);
    }

    /**
     * Test student cannot join the same class twice.
     */
    public function test_student_cannot_join_same_class_twice(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::factory()->create();

        $student->classes()->attach($class->id, ['grade' => $class->grade]);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'You have already joined this class',
                'error_code' => 'ALREADY_JOINED',
            ]);
    }

    /**
     * Test joining with an invalid class code returns validation error.
     */
    public function test_join_class_with_invalid_code_returns_validation_error(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/classes/join', [
            'class_code' => 'INVALID123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('class_code');
    }

    /**
     * Test joining without class code returns validation error.
     */
    public function test_join_class_without_code_returns_validation_error(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/v1/classes/join', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('class_code');
    }

    /**
     * Test teacher cannot join a class.
     */
    public function test_teacher_cannot_join_class(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::factory()->create();

        Sanctum::actingAs($teacher);

        $response = $this->postJson('/api/v1/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot join a class.
     */
    public function test_unauthenticated_user_cannot_join_class(): void
    {
        $class = SchoolClass::factory()->create();

        $response = $this->postJson('/api/v1/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test student can leave a class.
     */
    public function test_student_can_leave_class(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::factory()->create();

        $student->classes()->attach($class->id, ['grade' => $class->grade]);

        Sanctum::actingAs($student);

        $response = $this->deleteJson("/api/v1/classes/{$class->id}/leave");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully left the class',
            ])
            ->assertJsonPath('data.class_id', $class->id);

        $this->assertDatabaseMissing('class_student', [
            'student_id' => $student->id,
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test student cannot leave a class they are not a member of.
     */
    public function test_student_cannot_leave_class_not_a_member(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::factory()->create();

        Sanctum::actingAs($student);

        $response = $this->deleteJson("/api/v1/classes/{$class->id}/leave");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'You are not a member of this class',
                'error_code' => 'NOT_A_MEMBER',
            ]);
    }

    /**
     * Test teacher cannot leave a class.
     */
    public function test_teacher_cannot_leave_class(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $class = SchoolClass::factory()->create();

        Sanctum::actingAs($teacher);

        $response = $this->deleteJson("/api/v1/classes/{$class->id}/leave");

        $response->assertStatus(403);
    }

    /**
     * Test student can join multiple classes.
     */
    public function test_student_can_join_multiple_classes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $classA = SchoolClass::factory()->create(['grade' => 10]);
        $classB = SchoolClass::factory()->create(['grade' => 11]);

        Sanctum::actingAs($student);

        $responseA = $this->postJson('/api/v1/classes/join', [
            'class_code' => $classA->class_code,
        ]);
        $responseA->assertStatus(200);

        $responseB = $this->postJson('/api/v1/classes/join', [
            'class_code' => $classB->class_code,
        ]);
        $responseB->assertStatus(200);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $student->id,
            'class_id' => $classA->id,
        ]);

        $this->assertDatabaseHas('class_student', [
            'student_id' => $student->id,
            'class_id' => $classB->id,
        ]);
    }
}
