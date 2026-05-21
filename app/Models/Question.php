<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['learning_material_id', 'type', 'question_text', 'options', 'correct_answer'])]
class Question extends Model
{
    use HasFactory;

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the learning material this question belongs to.
     */
    public function learningMaterial(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }
}
