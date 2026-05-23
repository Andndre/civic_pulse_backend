<?php

namespace Tests\Feature;

use App\Models\AnecdotalNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnecdotalNotesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test list notes (index) access controls.
     */
    public function test_list_anecdotal_notes_authorization(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        AnecdotalNote::factory()->count(3)->create(['student_id' => $student->id]);

        // 1. Unauthenticated -> 401
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes");
        $response->assertStatus(401);

        // 2. Authenticated as another student -> 403
        $anotherStudent = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($anotherStudent);
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes");
        $response->assertStatus(403);

        // 3. Authenticated as teacher -> 200
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);

        // 4. Authenticated as admin -> 200
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes");
        $response->assertStatus(200);
    }

    /**
     * Test listing with search, sorting, and pagination.
     */
    public function test_list_anecdotal_notes_features(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);

        // Create specific notes for search/sort testing
        $note1 = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'content' => 'First observational note about Budi participating well.',
            'created_at' => now()->subDays(2),
        ]);

        $note2 = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'content' => 'Second observation. Budi is passive today.',
            'created_at' => now()->subDay(),
        ]);

        // 1. Check Search
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes?search=passive");
        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $note2->id);

        // 2. Check Sorting
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes?sort=created_at&order=asc");
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $note1->id)
            ->assertJsonPath('data.1.id', $note2->id);

        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes?sort=created_at&order=desc");
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $note2->id)
            ->assertJsonPath('data.1.id', $note1->id);
    }

    /**
     * Test list notes returns 404 for invalid students.
     */
    public function test_list_anecdotal_notes_for_invalid_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);

        // 1. Non-existent student ID -> 404 Student not found
        $response = $this->getJson('/api/v1/students/9999/anecdotal-notes');
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ]);

        // 2. User exists but role is not student -> 404 Student not found
        $anotherTeacher = User::factory()->create(['role' => 'teacher']);
        $response = $this->getJson("/api/v1/students/{$anotherTeacher->id}/anecdotal-notes");
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ]);
    }

    /**
     * Test creating anecdotal notes.
     */
    public function test_create_anecdotal_note(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        // 1. Unauthorized user (student) -> 403
        Sanctum::actingAs($student);
        $response = $this->postJson("/api/v1/students/{$student->id}/anecdotal-notes", [
            'content' => 'Teacher comment',
        ]);
        $response->assertStatus(403);

        // 2. Authorized user (teacher) -> 201
        Sanctum::actingAs($teacher);
        $response = $this->postJson("/api/v1/students/{$student->id}/anecdotal-notes", [
            'content' => 'Budi demonstrated great leadership skills during the civic project.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Anecdotal note created successfully')
            ->assertJsonStructure(['data' => ['id', 'student_id', 'teacher_id', 'content', 'created_at']])
            ->assertJsonPath('data.content', 'Budi demonstrated great leadership skills during the civic project.')
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.teacher_id', $teacher->id);

        $this->assertDatabaseHas('anecdotal_notes', [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'content' => 'Budi demonstrated great leadership skills during the civic project.',
        ]);

        // 3. Validation failed (empty content) -> 422
        $response = $this->postJson("/api/v1/students/{$student->id}/anecdotal-notes", [
            'content' => '',
        ]);
        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'VALIDATION_FAILED');
    }

    /**
     * Test viewing a specific anecdotal note.
     */
    public function test_show_anecdotal_note(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $note = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);

        Sanctum::actingAs($teacher);

        // 1. Success view
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes/{$note->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $note->id)
            ->assertJsonPath('data.content', $note->content);

        // 2. Note not found -> 404
        $response = $this->getJson("/api/v1/students/{$student->id}/anecdotal-notes/9999");
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Anecdotal note not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ]);

        // 3. Note exists but belongs to a different student -> 404
        $anotherStudent = User::factory()->create(['role' => 'student']);
        $response = $this->getJson("/api/v1/students/{$anotherStudent->id}/anecdotal-notes/{$note->id}");
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Anecdotal note not found for this student',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ]);
    }

    /**
     * Test updating an anecdotal note.
     */
    public function test_update_anecdotal_note(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $note = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'content' => 'Old content.',
        ]);

        Sanctum::actingAs($teacher);

        // 1. Success update
        $response = $this->putJson("/api/v1/students/{$student->id}/anecdotal-notes/{$note->id}", [
            'content' => 'Updated observation content.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.content', 'Updated observation content.');

        $this->assertDatabaseHas('anecdotal_notes', [
            'id' => $note->id,
            'content' => 'Updated observation content.',
        ]);

        // 2. Try to update a note belonging to a different student -> 404
        $anotherStudent = User::factory()->create(['role' => 'student']);
        $response = $this->putJson("/api/v1/students/{$anotherStudent->id}/anecdotal-notes/{$note->id}", [
            'content' => 'Should fail.',
        ]);
        $response->assertStatus(404);
    }

    /**
     * Test deleting an anecdotal note.
     */
    public function test_delete_anecdotal_note(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $note = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);

        Sanctum::actingAs($teacher);

        // 1. Try to delete a note belonging to a different student -> 404
        $anotherStudent = User::factory()->create(['role' => 'student']);
        $response = $this->deleteJson("/api/v1/students/{$anotherStudent->id}/anecdotal-notes/{$note->id}");
        $response->assertStatus(404);
        $this->assertDatabaseHas('anecdotal_notes', ['id' => $note->id]);

        // 2. Success delete with standard JSON response
        $response = $this->deleteJson("/api/v1/students/{$student->id}/anecdotal-notes/{$note->id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Anecdotal note deleted successfully');

        $this->assertDatabaseMissing('anecdotal_notes', ['id' => $note->id]);

        // 3. Success delete with no content header (X-Return-No-Content)
        $newNote = AnecdotalNote::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);
        $response = $this->deleteJson(
            "/api/v1/students/{$student->id}/anecdotal-notes/{$newNote->id}",
            [],
            ['X-Return-No-Content' => 'true']
        );
        $response->assertStatus(204);
        $this->assertDatabaseMissing('anecdotal_notes', ['id' => $newNote->id]);
    }
}
