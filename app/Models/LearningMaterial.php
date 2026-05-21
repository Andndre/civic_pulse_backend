<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'description', 'grade', 'file_url', 'created_by'])]
class LearningMaterial extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the user who created this material.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the test responses for this learning material.
     */
    public function testResponses(): HasMany
    {
        return $this->hasMany(TestResponse::class, 'learning_material_id');
    }

    /**
     * Get the pulse instruments for this learning material.
     */
    public function pulseInstruments(): HasMany
    {
        return $this->hasMany(PulseInstrument::class, 'learning_material_id');
    }

    /**
     * Get the questions for this learning material.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'learning_material_id');
    }
}
