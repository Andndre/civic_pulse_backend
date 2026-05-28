<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\PulseInstrument;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student learning path endpoints.
     */
    public function test_student_learning_path_flow(): void
    {
        // 1. Setup student, material, questions, and pulse instruments
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student);

        $material = LearningMaterial::factory()->create(['grade' => 7]);

        $preQuestion = Question::create([
            'learning_material_id' => $material->id,
            'type' => 'pre_test',
            'question_text' => 'Pernyataan Soal Pre-Test 1',
            'options' => ['Homogenisasi', 'Toleransi dan keberagaman', 'Eksklusivisme', 'Asimilasi paksa'],
            'correct_answer' => 'B',
        ]);

        $postQuestion = Question::create([
            'learning_material_id' => $material->id,
            'type' => 'post_test',
            'question_text' => 'Pernyataan Soal Post-Test 1',
            'options' => ['Satu tetap satu', 'Berbeda-beda tetapi tetap satu jua', 'Bersatu kita teguh', 'Keadilan sosial bagi seluruh rakyat'],
            'correct_answer' => 'B',
        ]);

        $pulseInst = PulseInstrument::create([
            'learning_material_id' => $material->id,
            'dimension' => 'P',
            'statement' => 'Saya aktif memberikan tanggapan.',
        ]);

        // 2. Test MaterialResource initially (pre_test = available, others locked)
        $response = $this->getJson("/api/v1/materials/{$material->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.learning_path_status.pre_test', 'available')
            ->assertJsonPath('data.learning_path_status.ebook', 'locked')
            ->assertJsonPath('data.learning_path_status.post_test', 'locked')
            ->assertJsonPath('data.learning_path_status.pulse', 'locked')
            ->assertJsonPath('data.student_score.pre_test_score', null);

        // 3. Test getQuestions for pre_test
        $response = $this->getJson("/api/v1/materials/{$material->id}/questions?type=pre");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.content', 'Pernyataan Soal Pre-Test 1')
            ->assertJsonPath('data.0.options.B', 'Toleransi dan keberagaman');

        // 4. Test submitTestResponse for pre_test
        $response = $this->postJson("/api/v1/materials/{$material->id}/test-response", [
            'type' => 'pre',
            'answers' => [
                [
                    'question_id' => $preQuestion->id,
                    'answer' => 'B',
                ],
            ],
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.score', 100)
            ->assertJsonPath('data.comparison.pre_score', 100);

        // 5. Test MaterialResource after pre_test (pre_test = completed, ebook = completed, post_test = available)
        $response = $this->getJson("/api/v1/materials/{$material->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.learning_path_status.pre_test', 'completed')
            ->assertJsonPath('data.learning_path_status.ebook', 'completed')
            ->assertJsonPath('data.learning_path_status.post_test', 'available')
            ->assertJsonPath('data.student_score.pre_test_score', 100);

        // 6. Test submitTestResponse for post_test
        $response = $this->postJson("/api/v1/materials/{$material->id}/test-response", [
            'type' => 'post',
            'answers' => [
                [
                    'question_id' => $postQuestion->id,
                    'answer' => 'A',
                ],
            ],
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.score', 0)
            ->assertJsonPath('data.comparison.post_score', 0);

        // 7. Test MaterialResource after post_test (post_test = completed, pulse = available)
        $response = $this->getJson("/api/v1/materials/{$material->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.learning_path_status.post_test', 'completed')
            ->assertJsonPath('data.learning_path_status.pulse', 'available')
            ->assertJsonPath('data.student_score.post_test_score', 0);

        // 8. Test getPulseStatements
        $response = $this->getJson("/api/v1/materials/{$material->id}/pulse-statements");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.dimension', 'participation');

        // 9. Test submitPulseResponse
        $response = $this->postJson("/api/v1/materials/{$material->id}/pulse-response", [
            'responses' => [
                [
                    'statement_id' => $pulseInst->id,
                    'score' => 5,
                ],
            ],
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // 10. Test MaterialResource after pulse (pulse = completed, material status = completed)
        $response = $this->getJson("/api/v1/materials/{$material->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.learning_path_status.pulse', 'completed')
            ->assertJsonPath('data.status', 'completed');
    }
}
