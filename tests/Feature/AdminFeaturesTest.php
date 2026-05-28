<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Admin Dashboard Stats endpoint.
     */
    public function test_admin_dashboard_stats(): void
    {
        // Create 2 teachers, 3 active students, 1 inactive student, 4 learning materials
        $teacher1 = User::factory()->create(['role' => 'teacher']);
        $teacher2 = User::factory()->create(['role' => 'teacher']);
        User::factory()->count(3)->create(['role' => 'student', 'status' => 'active']);
        User::factory()->count(1)->create(['role' => 'student', 'status' => 'inactive']);
        LearningMaterial::factory()->count(4)->create(['created_by' => $teacher1->id]);

        // 1. Student access - 403 Forbidden
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student);

        $response = $this->getJson('/api/v1/admin/dashboard/stats');
        $response->assertStatus(403);

        // 2. Admin access - 200 OK
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Dynamically calculate expected counts to be resilient to any background/setup records
        $expectedTeachers = User::where('role', 'teacher')->count();
        $expectedActiveStudents = User::where('role', 'student')->where('status', 'active')->count();
        $expectedInactiveStudents = User::where('role', 'student')->where('status', 'inactive')->count();
        $expectedMaterials = LearningMaterial::count();

        $response = $this->getJson('/api/v1/admin/dashboard/stats');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_teachers' => $expectedTeachers,
                    'total_students_active' => $expectedActiveStudents,
                    'total_students_inactive' => $expectedInactiveStudents,
                    'total_materials' => $expectedMaterials,
                ],
            ]);
    }

    /**
     * Test Admin User Management CRUD.
     */
    public function test_admin_user_management_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // 1. Create a user via Admin
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Alice AdminCreated',
            'email' => 'alice@example.com',
            'password' => 'secret123',
            'role' => 'teacher',
            'phone' => '+62811111111',
            'address' => 'Jl. Merdeka No. 1',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alice AdminCreated')
            ->assertJsonPath('data.role', 'teacher')
            ->assertJsonStructure(['data' => ['email_verified_at']]); // Auto-verified

        $userId = $response->json('data.id');

        // Verify password hashed correctly
        $user = User::find($userId);
        $this->assertTrue(\Hash::check('secret123', $user->password));

        // 2. Index listing
        $response = $this->getJson('/api/v1/admin/users?filter[role]=teacher');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data'); // Exactly Alice
        $response->assertJsonPath('meta.total', 1);

        // 3. Update user
        $response = $this->patchJson("/api/v1/admin/users/{$userId}", [
            'name' => 'Alice Updated',
            'status' => 'inactive',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Alice Updated')
            ->assertJsonPath('data.status', 'inactive');

        // 4. Toggle Status manually
        $response = $this->patchJson("/api/v1/admin/users/{$userId}/status", [
            'status' => 'locked',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'locked');

        // 5. Verify email manually
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);
        $response = $this->postJson("/api/v1/admin/users/{$unverifiedUser->id}/verify-email");
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['email_verified_at']]);

        // 6. Delete User (Soft Delete)
        $response = $this->deleteJson("/api/v1/admin/users/{$userId}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('users', ['id' => $userId]);

        // 7. Force Delete (on an active user to test force delete capability)
        $userForForceDelete = User::factory()->create(['role' => 'student']);
        $response = $this->deleteJson("/api/v1/admin/users/{$userForForceDelete->id}?force=true");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userForForceDelete->id]);
    }

    /**
     * Test Quiz Builder (Questions CRUD).
     */
    public function test_quiz_builder_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $material = LearningMaterial::factory()->create(['grade' => 8]);

        // 1. Create Question
        $response = $this->postJson('/api/v1/questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'What is multiculturalism?',
            'options' => ['A answer', 'B answer', 'C answer', 'D answer'],
            'correct_answer' => 'A',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.question_text', 'What is multiculturalism?')
            ->assertJsonPath('data.type', 'pre_test')
            ->assertJsonPath('data.correct_answer', 'A')
            ->assertJsonCount(4, 'data.options');

        $questionId = $response->json('data.id');

        // 2. Update Question
        $response = $this->patchJson("/api/v1/questions/{$questionId}", [
            'question_text' => 'What is multiculturalism updated?',
            'correct_answer' => 'B',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.question_text', 'What is multiculturalism updated?')
            ->assertJsonPath('data.correct_answer', 'B');

        // 3. Delete Question
        $response = $this->deleteJson("/api/v1/questions/{$questionId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
    }

    /**
     * Test Question Validation for Correct Answer Letter key.
     */
    public function test_question_validation_for_correct_answer_letter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $material = LearningMaterial::factory()->create(['grade' => 8]);

        // 1. Invalid option letter should fail
        $response = $this->postJson('/api/v1/questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'What is multiculturalism?',
            'options' => ['A answer', 'B answer', 'C answer', 'D answer'],
            'correct_answer' => 'E',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('correct_answer');

        // 2. Full text should fail (must be letter key)
        $response = $this->postJson('/api/v1/questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'What is multiculturalism?',
            'options' => ['A answer', 'B answer', 'C answer', 'D answer'],
            'correct_answer' => 'A answer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('correct_answer');

        // 3. Lowercase letter should work and convert to uppercase in DB
        $response = $this->postJson('/api/v1/questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'What is multiculturalism?',
            'options' => ['A answer', 'B answer', 'C answer', 'D answer'],
            'correct_answer' => 'b',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.correct_answer', 'B');
    }

    /**
     * Test PULSE Instrument Builder CRUD.
     */
    public function test_pulse_instrument_builder_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $material = LearningMaterial::factory()->create(['grade' => 9]);

        // 1. Create Instrument
        $response = $this->postJson('/api/v1/pulse-instruments', [
            'learning_material_id' => $material->id,
            'dimension' => 'SE',
            'statement' => 'I help friends from different backgrounds.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.dimension', 'SE')
            ->assertJsonPath('data.statement', 'I help friends from different backgrounds.');

        $instrumentId = $response->json('data.id');

        // 2. Update Instrument
        $response = $this->patchJson("/api/v1/pulse-instruments/{$instrumentId}", [
            'statement' => 'I actively help friends from different backgrounds.',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.statement', 'I actively help friends from different backgrounds.');

        // 3. Delete Instrument
        $response = $this->deleteJson("/api/v1/pulse-instruments/{$instrumentId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('pulse_instruments', ['id' => $instrumentId]);
    }

    /**
     * Test Learning Materials validation and security updates.
     */
    public function test_learning_materials_grade_validation_and_security(): void
    {
        // 1. Student cannot create learning material
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/materials', [
            'title' => 'Grade 7 Civics',
            'description' => 'Test',
            'grade' => 7,
            'file' => $file,
        ]);
        $response->assertStatus(403);

        // 2. Admin can create material with valid grade (7-12)
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/materials', [
            'title' => 'Grade 7 Civics',
            'description' => 'Test',
            'grade' => 7,
            'file' => $file,
        ]);
        $response->assertStatus(201);

        // 3. Admin validation fails for invalid grade (e.g. 6 or 13)
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $response = $this->postJson('/api/v1/materials', [
            'title' => 'Grade 6 Civics',
            'description' => 'Test',
            'grade' => 6,
            'file' => $file,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $response = $this->postJson('/api/v1/materials', [
            'title' => 'Grade 13 Civics',
            'description' => 'Test',
            'grade' => 13,
            'file' => $file,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);
    }

    /**
     * Test importing questions from XLSX file.
     */
    public function test_import_questions_from_xlsx(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $material = LearningMaterial::factory()->create(['grade' => 8]);

        // Use the generated XLSX pre test template
        $filePath = public_path('templates/pre_test_template.xlsx');
        $this->assertFileExists($filePath);

        $file = new UploadedFile(
            $filePath,
            'pre_test_template.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->postJson('/api/v1/questions/import', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify that database has the two questions from the template
        $this->assertDatabaseHas('questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'Contoh: Apa nilai utama dalam pendidikan multikultural?',
            'correct_answer' => 'B',
        ]);

        $this->assertDatabaseHas('questions', [
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'Contoh 2: Manakah yang mencerminkan partisipasi kewargaan yang aktif?',
            'correct_answer' => 'B',
        ]);
    }

    /**
     * Test importing pulse instruments from XLSX file.
     */
    public function test_import_pulse_instruments_from_xlsx(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $material = LearningMaterial::factory()->create(['grade' => 9]);

        // Use the generated XLSX pulse template
        $filePath = public_path('templates/pulse_instrument_template.xlsx');
        $this->assertFileExists($filePath);

        $file = new UploadedFile(
            $filePath,
            'pulse_instrument_template.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->postJson('/api/v1/pulse-instruments/import', [
            'learning_material_id' => $material->id,
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify that database has the instruments from the template
        $this->assertDatabaseHas('pulse_instruments', [
            'learning_material_id' => $material->id,
            'dimension' => 'P',
            'statement' => 'Saya aktif memberikan tanggapan/pendapat saat kerja kelompok.',
        ]);

        $this->assertDatabaseHas('pulse_instruments', [
            'learning_material_id' => $material->id,
            'dimension' => 'SE',
            'statement' => 'Saya bersedia membantu teman yang kesulitan tanpa membedakan suku/agama.',
        ]);
    }
}
