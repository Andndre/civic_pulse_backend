<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\LearningMaterial;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Authentication Endpoints.
     */
    public function test_authentication_endpoints(): void
    {
        // 1. Register
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'student',
            'phone' => '+6281234567890',
            'address' => 'Jl. Sudirman No. 123, Jakarta',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'token_type',
                    'expires_at',
                ],
            ]);

        // 2. Login
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);

        $token = $loginResponse->json('data.token');

        // 3. User Me Profile
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/users/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User profile retrieved successfully',
            ])
            ->assertJsonPath('data.email', 'john.doe@example.com');

        // 4. Token Refresh
        $refreshResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/auth/refresh');

        $refreshResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token refreshed successfully',
            ]);

        $newToken = $refreshResponse->json('data.token');

        // 5. Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$newToken,
        ])->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
    }

    /**
     * Test Custom Exceptions.
     */
    public function test_custom_exception_handling(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // 1. Model Binding 404 Structure
        $response = $this->getJson('/api/v1/students/9999');
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ]);

        // 2. Validation 422 Structure
        $response = $this->postJson('/api/v1/students', []);
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid',
                'error_code' => 'VALIDATION_FAILED',
            ])
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test Students CRUD.
     */
    public function test_students_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $class = SchoolClass::factory()->create(['grade' => 10]);

        // 1. Create Student
        $storeResponse = $this->postJson('/api/v1/students', [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'phone' => '+6281234567892',
            'address' => 'Jl. Gatot Subroto No. 456, Jakarta',
            'date_of_birth' => '2008-05-20',
            'gender' => 'female',
            'class_id' => $class->id,
            'parent_name' => 'Michael Doe',
            'parent_phone' => '+6281234567893',
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Jane Doe')
            ->assertJsonPath('data.class_id', $class->id);

        $studentId = $storeResponse->json('data.id');

        // Check pivot
        $this->assertDatabaseHas('class_student', [
            'student_id' => $studentId,
            'class_id' => $class->id,
            'grade' => 10,
        ]);

        // 2. Get Student details
        $showResponse = $this->getJson("/api/v1/students/{$studentId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.class.id', $class->id)
            ->assertJsonPath('data.class.homeroom_teacher', $class->homeroomTeacher->name);

        // 3. Update Student (PATCH)
        $patchResponse = $this->patchJson("/api/v1/students/{$studentId}", [
            'phone' => '+628111111111',
        ]);
        $patchResponse->assertStatus(200)
            ->assertJsonPath('data.phone', '+628111111111');

        // 4. Index listing, with filters and search
        $indexResponse = $this->getJson('/api/v1/students?search=Jane&filter[class_id]='.$class->id);
        $indexResponse->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $studentId);

        // 5. Delete (Soft Delete)
        $deleteResponse = $this->deleteJson("/api/v1/students/{$studentId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Student deleted successfully');

        $this->assertSoftDeleted('users', ['id' => $studentId]);

        // 6. Restore
        $restoreResponse = $this->postJson("/api/v1/students/{$studentId}/restore");
        $restoreResponse->assertStatus(200)
            ->assertJsonPath('message', 'Student restored successfully');

        $this->assertNotSoftDeleted('users', ['id' => $studentId]);

        // 7. Dependency check: Add activity
        $activity = ActivityLog::factory()->create(['student_id' => $studentId]);
        $deleteResponse = $this->deleteJson("/api/v1/students/{$studentId}");
        $deleteResponse->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete student with associated activities',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => ['activities'],
            ]);

        // Delete activity to allow deletion
        $activity->forceDelete();

        // 8. Delete (Force Delete)
        $deleteResponse = $this->deleteJson("/api/v1/students/{$studentId}?force=true");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Student permanently deleted');

        $this->assertDatabaseMissing('users', ['id' => $studentId]);
    }

    /**
     * Test Teachers CRUD.
     */
    public function test_teachers_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // 1. Create Teacher
        $storeResponse = $this->postJson('/api/v1/teachers', [
            'name' => 'Mrs. Jane Wilson',
            'email' => 'jane.wilson@example.com',
            'password' => 'password123',
            'phone' => '+6281234567894',
            'address' => 'Jl. Kebon Sirih No. 12, Jakarta',
            'date_of_birth' => '1985-08-15',
            'gender' => 'female',
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Mrs. Jane Wilson');

        $teacherId = $storeResponse->json('data.id');

        // 2. Update Teacher (PUT)
        $updateResponse = $this->putJson("/api/v1/teachers/{$teacherId}", [
            'name' => 'Mrs. Jane Wilson Updated',
            'email' => 'jane.wilson@example.com',
            'status' => 'active',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Mrs. Jane Wilson Updated');

        // 3. Dependency check: Make them a homeroom teacher
        $class = SchoolClass::factory()->create(['homeroom_teacher_id' => $teacherId]);
        $deleteResponse = $this->deleteJson("/api/v1/teachers/{$teacherId}");
        $deleteResponse->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete teacher with associated classes',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => ['classes'],
            ]);

        // Detach homeroom teacher to allow deletion
        $class->update(['homeroom_teacher_id' => null]);

        // 4. Delete Teacher
        $deleteResponse = $this->deleteJson("/api/v1/teachers/{$teacherId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Teacher deleted successfully');
    }

    /**
     * Test Classes CRUD.
     */
    public function test_classes_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $teacher = User::factory()->create(['role' => 'teacher']);

        // 1. Create Class
        $storeResponse = $this->postJson('/api/v1/classes', [
            'name' => 'Grade 10-A',
            'grade' => 10,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'Grade 10-A')
            ->assertJsonStructure(['data' => ['class_code']]);

        $classId = $storeResponse->json('data.id');

        // 2. Update Class
        $updateResponse = $this->putJson("/api/v1/classes/{$classId}", [
            'name' => 'Grade 10-A Updated',
            'grade' => 10,
            'homeroom_teacher_id' => $teacher->id,
            'class_code' => $storeResponse->json('data.class_code'),
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Grade 10-A Updated');

        // 3. Delete Class
        $deleteResponse = $this->deleteJson("/api/v1/classes/{$classId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Class deleted successfully');
    }

    /**
     * Test Activities CRUD.
     */
    public function test_activities_crud(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student);

        // 1. Create Activity
        $storeResponse = $this->postJson('/api/v1/activities', [
            'student_id' => $student->id,
            'title' => 'Science Fair 2026',
            'description' => 'Participated in regional science fair',
            'type' => 'competition',
            'date' => '2026-05-15',
            'location' => 'Jakarta Convention Center',
            'achievement' => 'First Place',
            'points' => 100,
            'evidence_url' => 'https://example.com/certificate.pdf',
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Science Fair 2026')
            ->assertJsonPath('data.status', 'pending');

        $activityId = $storeResponse->json('data.id');

        // 2. Attempt to update status as student - Should fail with 403 Forbidden
        $statusResponse = $this->patchJson("/api/v1/activities/{$activityId}/status", [
            'status' => 'approved',
            'review_notes' => 'Great work!',
        ]);
        $statusResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have permission to access this resource',
                'error_code' => 'FORBIDDEN',
            ]);

        // 3. Log in as Teacher and Approve
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);

        $statusResponse = $this->patchJson("/api/v1/activities/{$activityId}/status", [
            'status' => 'approved',
            'review_notes' => 'Verified with competition results',
        ]);
        $statusResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.reviewed_by', $teacher->id);

        // 4. Delete Activity
        $deleteResponse = $this->deleteJson("/api/v1/activities/{$activityId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Activity deleted successfully');
    }

    /**
     * Test Scores CRUD.
     */
    public function test_scores_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $student = User::factory()->create(['role' => 'student']);
        $material = LearningMaterial::factory()->create();

        // 1. Create Score
        $storeResponse = $this->postJson('/api/v1/scores', [
            'student_id' => $student->id,
            'learning_material_id' => $material->id,
            'type' => 'post_test',
            'score' => 92,
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.score', 92)
            ->assertJsonPath('data.grade', 'A');

        $scoreId = $storeResponse->json('data.id');

        // 2. Update Score
        $updateResponse = $this->putJson("/api/v1/scores/{$scoreId}", [
            'student_id' => $student->id,
            'learning_material_id' => $material->id,
            'type' => 'post_test',
            'score' => 75,
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.score', 75)
            ->assertJsonPath('data.grade', 'C');

        // 3. Delete Score
        $deleteResponse = $this->deleteJson("/api/v1/scores/{$scoreId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Score deleted successfully');
    }

    /**
     * Test Materials CRUD.
     */
    public function test_materials_crud(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);

        // 1. Create Material
        $storeResponse = $this->postJson('/api/v1/materials', [
            'title' => 'Introduction to Civics',
            'description' => 'Basics of government structures.',
            'grade' => 10,
            'file_url' => 'https://example.com/civics-intro.pdf',
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Introduction to Civics');

        $materialId = $storeResponse->json('data.id');

        // 2. Update Material
        $updateResponse = $this->putJson("/api/v1/materials/{$materialId}", [
            'title' => 'Introduction to Civics Updated',
            'description' => 'Basics of government structures.',
            'grade' => 10,
            'file_url' => 'https://example.com/civics-intro.pdf',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Introduction to Civics Updated');

        // 3. Delete Material
        $deleteResponse = $this->deleteJson("/api/v1/materials/{$materialId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'Material deleted successfully');
    }
}
